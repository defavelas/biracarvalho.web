<?php

declare(strict_types=1);

namespace App\Services\Kobo;

use App\Adapter\Kobo\NonAccessibleLocationAdapter;
use App\Models\Image;
use App\Models\Info;
use App\Models\Location;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class NonAccessibleLocationsProcess
{
    public function __construct(
        private readonly NonAccessibleLocationsService $nonAccessibleLocationsService,
        private readonly NonAccessibleLocationAdapter $adapter,
    ) {}

    /**
     * Process and persist all non-accessible locations from Kobo.
     *
     * @return array{processed: int, created: int, updated: int, skipped: int, errors: int}
     */
    public function processAll(): array
    {
        Log::info('Starting non-accessible locations processing');

        $stats = [
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        try {
            $koboData = $this->nonAccessibleLocationsService->fetchAllData();

            foreach ($koboData as $record) {
                try {
                    $result = $this->processRecord($record);
                    $stats[$result]++;
                    $stats['processed']++;

                } catch (Exception $e) {
                    Log::error('Failed to process record', [
                        'record_id' => $record['_id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);
                    $stats['errors']++;
                }
            }

        } catch (Exception $e) {
            Log::error('Failed to fetch non-accessible locations data', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        Log::info('Completed non-accessible locations processing', $stats);
        return $stats;
    }

    /**
     * Process a single Kobo record.
     *
     * @param array<string, mixed> $koboRecord
     * @return string 'created', 'updated', or 'skipped'
     */
    public function processRecord(array $koboRecord): string
    {
        $externalId = (string) ($koboRecord['_id'] ?? '');

        if (empty($externalId)) {
            throw new InvalidArgumentException('Record missing _id');
        }

        // Transform the data
        $transformedData = $this->adapter->transform($koboRecord);

        // Check if location already exists
        $existingLocation = Location::where('external_id', $externalId)->first();

        if ($existingLocation) {
            return $this->updateLocation($existingLocation, $transformedData);
        }

        return $this->createLocation($transformedData);
    }

    /**
     * Create a new location with related data.
     *
     * @param array{location: array<string, mixed>, images: array<int, array<string, mixed>>, infos: array<int, array<string, mixed>>} $data
     */
    private function createLocation(array $data): string
    {
        return DB::transaction(function () use ($data) {
            // Create location
            $location = Location::create($data['location']);

            Log::info('Created new non-accessible location', [
                'id' => $location->id,
                'external_id' => $location->external_id,
                'name' => $location->name,
            ]);

            // Create images
            $this->createImages($location, $data['images']);

            // Create infos
            $this->createInfos($location, $data['infos']);

            return 'created';
        });
    }

    /**
     * Update an existing location with new data.
     *
     * @param Location $location
     * @param array{location: array<string, mixed>, images: array<int, array<string, mixed>>, infos: array<int, array<string, mixed>>} $data
     */
    private function updateLocation(Location $location, array $data): string
    {
        return DB::transaction(function () use ($location, $data) {
            // Update location (preserve published_at if already set)
            $locationData = $data['location'];
            if ($location->published_at) {
                unset($locationData['published_at']);
            }

            $location->update($locationData);

            Log::info('Updated existing non-accessible location', [
                'id' => $location->id,
                'external_id' => $location->external_id,
                'name' => $location->name,
            ]);

            // Update images (simple approach: delete existing and recreate)
            $location->images()->delete();
            $this->createImages($location, $data['images']);

            // Update infos (simple approach: delete existing and recreate)
            $location->infos()->delete();
            $this->createInfos($location, $data['infos']);

            return 'updated';
        });
    }

    /**
     * Create image records for a location.
     *
     * @param Location $location
     * @param array<int, array<string, mixed>> $imagesData
     */
    private function createImages(Location $location, array $imagesData): void
    {
        foreach ($imagesData as $imageData) {
            try {
                // Download and store the image file
                $downloadUrl = $imageData['download_url'] ?? null;
                if ( ! $downloadUrl) {
                    continue;
                }

                $imagePath = $this->downloadAndStoreImage($downloadUrl, $imageData['mimetype'] ?? 'image/jpeg');

                if ($imagePath) {
                    Image::create([
                        'location_id' => $location->id,
                        'image_path' => $imagePath,
                        'published_at' => null,
                    ]);

                    Log::debug('Created public image for non-accessible location', [
                        'location_id' => $location->id,
                        'image_path' => $imagePath,
                        'public_url' => Storage::url($imagePath),
                    ]);
                }

            } catch (Exception $e) {
                Log::warning('Failed to download image for non-accessible location', [
                    'location_id' => $location->id,
                    'download_url' => $imageData['download_url'] ?? '',
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create info records for a location.
     *
     * @param Location $location
     * @param array<int, array<string, mixed>> $infosData
     */
    private function createInfos(Location $location, array $infosData): void
    {
        foreach ($infosData as $infoData) {
            if ( ! empty($infoData['title']) && ! empty($infoData['value'])) {
                Info::create([
                    'location_id' => $location->id,
                    'title' => $infoData['title'],
                    'value' => $infoData['value'],
                ]);
            }
        }

        Log::debug('Created infos for non-accessible location', [
            'location_id' => $location->id,
            'count' => count($infosData),
        ]);
    }

    /**
     * Download and store an image from URL.
     */
    private function downloadAndStoreImage(string $url, string $mimeType): ?string
    {
        try {
            // Add authorization header for Kobo API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('kobo.api_token'),
            ])->timeout(30)->get($url);

            if ( ! $response->successful()) {
                throw new Exception("HTTP {$response->status()}: {$response->body()}");
            }

            // Generate file path for public access
            $extension = $this->getExtensionFromMimeType($mimeType);
            $filename = Str::uuid() . '.' . $extension;
            $path = 'public/images/locations/' . $filename;

            // Store the file in public disk
            Storage::put($path, $response->body());

            return $path;

        } catch (Exception $e) {
            Log::error('Failed to download image for non-accessible location', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get file extension from MIME type.
     */
    private function getExtensionFromMimeType(string $mimeType): string
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/heic' => 'heic',
        ];

        return $extensions[$mimeType] ?? 'jpg';
    }
}
