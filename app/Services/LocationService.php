<?php

declare(strict_types=1);

namespace App\Services;

use App\Enum\Location\Type;
use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class LocationService
{
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Get all published locations with caching for map performance.
     *
     * @return Collection<int, Location>
     */
    public function getPublishedLocationsForMap(): Collection
    {
        return Cache::remember('locations.published.map', self::CACHE_TTL, function () {
            Log::info('Loading published locations for map from database');

            return Location::query()
                ->published()
                ->with(['images', 'infos'])
                ->select([
                    'id',
                    'name',
                    'description',
                    'latitude',
                    'longitude',
                    'type',
                    'authors',
                    'published_at',
                    'created_at',
                ])
                ->orderBy('published_at', 'desc')
                ->get();
        });
    }

    /**
     * Search locations with filters and caching.
     *
     * @param string $search
     * @param array<string> $categories
     * @return Collection<int, Location>
     */
    public function searchLocations(string $search = '', array $categories = []): Collection
    {
        $cacheKey = $this->generateSearchCacheKey($search, $categories);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($search, $categories) {
            Log::debug('Searching locations in database', [
                'search' => $search,
                'categories' => $categories,
            ]);

            $query = Location::query()
                ->published()
                ->with(['images', 'infos']);

            if ( ! empty($search)) {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('authors', 'like', "%{$search}%");
                });
            }

            if ( ! empty($categories)) {
                $query->whereIn('type', $categories);
            }

            return $query
                ->orderBy('published_at', 'desc')
                ->get();
        });
    }

    /**
     * Get a specific location by ID with caching.
     *
     * @param string $locationId
     * @return Location|null
     */
    public function getLocationById(string $locationId): ?Location
    {
        return Cache::remember("location.{$locationId}", self::CACHE_TTL, fn() => Location::query()
            ->published()
            ->with(['images', 'infos'])
            ->find($locationId));
    }

    /**
     * Get location statistics for dashboard.
     *
     * @return array{total: int, published: int, pending: int, by_type: array<string, int>}
     */
    public function getLocationStats(): array
    {
        return Cache::remember('locations.stats', self::CACHE_TTL, function () {
            $total = Location::count();
            $published = Location::published()->count();
            $pending = Location::pending()->count();

            $byType = [];
            foreach (Type::cases() as $type) {
                $byType[$type->value] = Location::published()
                    ->where('type', $type->value)
                    ->count();
            }

            return [
                'total' => $total,
                'published' => $published,
                'pending' => $pending,
                'by_type' => $byType,
            ];
        });
    }

    /**
     * Transform location for map marker data.
     *
     * @param Location $location
     * @return array<string, mixed>
     */
    public function transformForMap(Location $location): array
    {
        $imageAltTexts = $this->buildImageAltTexts($location);

        return [
            'id' => $location->id,
            'name' => $location->name,
            'description' => $location->description,
            'typeLabel' => $location->type->label(),
            'typeColor' => $location->type->color(),
            'latitude' => (float) $location->latitude,
            'longitude' => (float) $location->longitude,
            'type' => $location->type,
            'authors' => $location->authors,
            'publishedAt' => $location->published_at?->format('d/m/Y H:i'),
            'createdAt' => $location->created_at->format('d/m/Y H:i'),
            'images' => $location->images->map(fn($image) => [
                'id' => $image->id,
                'path' => $image->image_path,
                'url' => asset('storage/' . $image->image_path),
                'alt' => $imageAltTexts[$image->id] ?? $this->buildFallbackImageAltText($location),
            ])->toArray(),
            'infos' => $location->infos->map(fn($info) => [
                'id' => $info->id,
                'title' => $info->title,
                'value' => $info->value,
            ])->toArray(),
        ];
    }

    /**
     * Transform multiple locations for map.
     *
     * @param Collection<int, Location> $locations
     * @return array<int, array<string, mixed>>
     */
    public function transformCollectionForMap(Collection $locations): array
    {
        return $locations->map(fn($location) => $this->transformForMap($location))->toArray();
    }

    /**
     * Clear location caches.
     */
    public function clearCache(): void
    {
        Cache::forget('locations.published.map');
        Cache::forget('locations.stats');

        $tags = ['locations.search'];
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags($tags)->flush();
        }

        Log::info('Location caches cleared');
    }

    /**
     * Generate cache key for search results.
     *
     * @param string $search
     * @param array<string> $categories
     */
    private function generateSearchCacheKey(string $search, array $categories): string
    {
        $searchHash = md5($search);
        $categories = is_array($categories) ? $categories : [];
        sort($categories);
        $categoriesHash = md5(implode(',', $categories));

        return "locations.search.{$searchHash}.{$categoriesHash}";
    }

    /**
     * @return array<string, string>
     */
    private function buildImageAltTexts(Location $location): array
    {
        $images = $location->images->values();
        $totalImages = $images->count();
        $infoSummaries = $this->buildInfoSummaries($location);

        return $images->mapWithKeys(function ($image, int $index) use ($location, $totalImages, $infoSummaries): array {
            $parts = [
                sprintf(
                    'Foto %d de %d do local %s',
                    $index + 1,
                    $totalImages,
                    $location->name,
                ),
            ];

            if ($scene = $this->buildSceneContext($location)) {
                $parts[] = $scene;
            }

            $parts[] = 'Registro do mapeamento de acessibilidade';

            if ($detail = $infoSummaries[$index] ?? $infoSummaries[0] ?? null) {
                $parts[] = 'com destaque para ' . $detail;
            } else {
                $parts[] = 'no contexto de ' . Str::lower($location->type->label());
            }

            return [
                $image->id => implode('. ', $parts) . '.',
            ];
        })->toArray();
    }

    /**
     * @return array<int, string>
     */
    private function buildInfoSummaries(Location $location): array
    {
        return $location->infos
            ->map(function ($info): ?string {
                $title = trim((string) $info->title);
                $value = trim((string) $info->value);

                if ('' === $title || '' === $value) {
                    return null;
                }

                return Str::limit("{$title}: {$value}", 140, '...');
            })
            ->filter()
            ->values()
            ->all();
    }

    private function buildSceneContext(Location $location): ?string
    {
        $description = trim((string) $location->description);

        if ('' === $description) {
            return null;
        }

        return 'Contexto do local: ' . Str::lower(Str::limit($description, 90, '...'));
    }

    private function buildFallbackImageAltText(Location $location): string
    {
        $parts = [
            "Foto do local {$location->name}",
            'registro do mapeamento de acessibilidade',
        ];

        if ($scene = $this->buildSceneContext($location)) {
            $parts[] = $scene;
        }

        return implode('. ', $parts) . '.';
    }
}
