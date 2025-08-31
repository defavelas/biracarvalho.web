<?php

declare(strict_types=1);

namespace App\Adapter\Kobo;

use App\Enum\Location\Type;
use Illuminate\Support\Str;

final class NonAccessibleLocationAdapter
{
    /**
     * Transform Kobo survey data into Location, Image, and Info model arrays.
     *
     * @param array<string, mixed> $koboData
     * @return array{location: array<string, mixed>, images: array<int, array<string, mixed>>, infos: array<int, array<string, mixed>>}
     */
    public function transform(array $koboData): array
    {
        return [
            'location' => $this->transformLocation($koboData),
            'images' => $this->transformImages($koboData),
            'infos' => $this->transformInfos($koboData),
        ];
    }

    /**
     * Transform Kobo data into Location model array.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function transformLocation(array $data): array
    {
        $coordinates = $this->extractCoordinates($data);

        return [
            'external_id' => (string) ($data['_id'] ?? ''),
            'name' => $this->extractLocationName($data),
            'description' => $this->extractLocationDescription($data),
            'type' => Type::NON_ACCESSIBLE,
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
            'authors' => $this->extractAuthors($data),
            'published_at' => null, // Set to null for moderation
        ];
    }

    /**
     * Transform attachments into Image model arrays.
     *
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    private function transformImages(array $data): array
    {
        $attachments = $data['_attachments'] ?? [];
        $images = [];

        foreach ($attachments as $attachment) {
            $downloadUrl = str_replace('/?format=json', '', $attachment['download_medium_url'] ?? '');

            // Only process image attachments
            if ($this->isImageMimeType($attachment['mimetype'] ?? '')) {
                $images[] = [
                    'image_path' => null, // Will be set when file is downloaded
                    'original_filename' => $attachment['filename'] ?? '',
                    'download_url' => $downloadUrl ?? '',
                    'mimetype' => $attachment['mimetype'] ?? '',
                    'published_at' => null,
                ];
            }
        }

        return $images;
    }

    /**
     * Transform question responses into Info model arrays.
     *
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    private function transformInfos(array $data): array
    {
        $infos = [];
        $excludedKeys = $this->getExcludedInfoKeys();

        foreach ($data as $key => $value) {
            // Skip system fields and fields already mapped to location
            if (in_array($key, $excludedKeys) || null === $value || '' === $value) {
                continue;
            }

            // Skip arrays and objects
            if (is_array($value)) {
                continue;
            }

            // Skip media files (check if value looks like a filename)
            if ($this->isMediaFile((string) $value)) {
                continue;
            }

            // Skip coordinate data (check if value looks like coordinates)
            if ($this->isCoordinateData((string) $value)) {
                continue;
            }

            $title = $this->decodeQuestionTitle($key);
            $cleanedValue = $this->cleanValue((string) $value);

            if (null !== $cleanedValue && '' !== $cleanedValue) {
                $infos[] = [
                    'title' => $title,
                    'value' => $cleanedValue,
                ];
            }
        }

        return $infos;
    }

    /**
     * Extract coordinates from geolocation data.
     *
     * @param array<string, mixed> $data
     * @return array{latitude: float|null, longitude: float|null}
     */
    private function extractCoordinates(array $data): array
    {
        $geolocation = $data['_geolocation'] ?? [];

        return [
            'latitude' => isset($geolocation[0]) && is_numeric($geolocation[0])
                ? (float) $geolocation[0]
                : null,
            'longitude' => isset($geolocation[1]) && is_numeric($geolocation[1])
                ? (float) $geolocation[1]
                : null,
        ];
    }

    /**
     * Extract location name from Kobo data.
     */
    private function extractLocationName(array $data): ?string
    {
        $rawName = $data['_2_Qual_o_nome_desse_lugar_ou_ponto'] ?? null;
        return $this->cleanLocationName($rawName);
    }

    /**
     * Extract location type from Kobo data.
     */
    private function extractLocationDescription(array $data): ?string
    {
        $rawType = $data['_3_Que_tipo_de_lugar_esse'] ?? null;
        return $this->cleanValue($rawType);
    }

    /**
     * Extract authors from Kobo data.
     */
    private function extractAuthors(array $data): ?string
    {
        return $this->cleanValue($data['_10_Nome_dos_dois_pe_aram_este_formul_rio'] ?? null);
    }

    /**
     * Check if mime type is an image.
     */
    private function isImageMimeType(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/');
    }

    /**
     * Get keys that should be excluded from Info records.
     *
     * @return array<string>
     */
    private function getExcludedInfoKeys(): array
    {
        return [
            // System fields
            '_id',
            '_uuid',
            '_submission_time',
            '_geolocation',
            '_attachments',
            '_status',
            '_tags',
            '_notes',
            '_validation_status',
            '_submitted_by',
            '_xform_id_string',
            '_supplementalDetails',
            'formhub/uuid',
            'start',
            'end',
            '__version__',
            'meta/instanceID',
            'meta/rootUuid',

            // Fields mapped to location model
            '_2_Qual_o_nome_desse_lugar_ou_ponto', // Mapped to location.name
            '_3_Que_tipo_de_lugar_esse', // Mapped to location.type
            '_10_Nome_dos_dois_pe_aram_este_formul_rio', // Mapped to location.authors

            // Coordinate fields (mapped to location.latitude/longitude)
            '_4_Compartilhe_a_localiza_o', // GPS coordinates
            '_6_Compartilhe_a_localiza_o', // GPS coordinates variant
            '_4_Compartilhe_a_localiza_o_001', // GPS coordinates variant

            // Media file fields (not survey questions)
            '_7_Tire_uma_foto_que_mostre_o_local', // Image filename
            '_8_Tire_mais_uma_fot_gora_de_outro_ngulo', // Image filename
            '_9_Grave_um_udio_ex_ugar_n_o_acess_vel', // Audio filename
        ];
    }

    /**
     * Decode question title from Kobo field name.
     */
    private function decodeQuestionTitle(string $key): string
    {
        // Basic decoding - replace underscores with spaces and clean up
        $title = str_replace('_', ' ', $key);
        $title = preg_replace('/^\d+\s+/', '', $title);
        $title = Str::title($title);

        // Get question title mappings from config
        $mappings = config('kobo.non_accessible', []);

        return $mappings[$key] ?? $title;
    }

    /**
     * Clean and format value for storage.
     */
    private function cleanValue(?string $value): ?string
    {
        if (null === $value || '' === mb_trim($value)) {
            return null;
        }

        // First try to decode using config mappings
        $decoded = $this->decodeValueFromConfig($value);
        if ($decoded !== $value) {
            return $decoded;
        }

        // Clean up common Kobo encoding issues
        $cleaned = str_replace('_', ' ', $value);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        $cleaned = mb_trim($cleaned);

        return '' !== $cleaned ? $cleaned : null;
    }

    /**
     * Decode value using config mappings for non-accessible locations.
     */
    private function decodeValueFromConfig(string $value): string
    {
        $mappings = config('kobo.non_accessible', []);

        // Try direct mapping first
        if (isset($mappings[$value])) {
            return $mappings[$value];
        }

        // For multi-value fields (space-separated), split and map each part
        if (str_contains($value, ' ')) {
            $parts = explode(' ', $value);
            $decodedParts = [];

            foreach ($parts as $part) {
                if ('' !== trim($part)) {
                    $decodedParts[] = $mappings[$part] ?? $this->fallbackDecode($part);
                }
            }

            return implode(', ', array_filter($decodedParts));
        }

        return $this->fallbackDecode($value);
    }

    /**
     * Fallback decoding for values not in config.
     */
    private function fallbackDecode(string $value): string
    {
        // Clean up common Kobo encoding patterns
        $decoded = str_replace('_', ' ', $value);
        $decoded = preg_replace('/\s+/', ' ', $decoded);
        $decoded = mb_trim($decoded);

        return '' !== $decoded ? $decoded : $value;
    }

    /**
     * Check if a value appears to be a media file.
     */
    private function isMediaFile(string $value): bool
    {
        // Check for common media file patterns
        $mediaPatterns = [
            '/\.(jpg|jpeg|png|gif|webp|heic)$/i', // Image extensions
            '/\.(mp3|m4a|wav|aac|opus|mov)$/i',   // Audio/video extensions
            '/^\d+.*\.(jpg|jpeg|png|gif|webp|heic|mp3|m4a|wav|aac|opus|mov)$/i', // Timestamped files
            '/^[A-Za-z]+.*\d+.*\.(jpg|jpeg|png|gif|webp|heic|mp3|m4a|wav|aac|opus|mov)$/i', // Named files with numbers
            '/^image-\d+/i', // Pattern like "image-12_24_41.jpg"
            '/^IMG_\d+/i',   // Pattern like "IMG_20250604_111609"
            '/Voz\s+\d+/i',  // Pattern like "Voz 250226_122459"
            '/C:\\\\fakepath\\\\/i', // Windows fake path
        ];

        foreach ($mediaPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a value appears to be coordinate data.
     */
    private function isCoordinateData(string $value): bool
    {
        // Check for coordinate patterns like "-22.855602 -43.247054 0.9000000357627869 100"
        $coordinatePatterns = [
            '/^-?\d+\.\d+\s+-?\d+\.\d+/', // Latitude longitude pattern
            '/^-?\d+\.\d+\s+-?\d+\.\d+\s+[\d\.\-]+/', // With additional precision/altitude data
        ];

        foreach ($coordinatePatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Clean and format location name specifically.
     */
    private function cleanLocationName(?string $name): ?string
    {
        if (null === $name || '' === mb_trim($name)) {
            return null;
        }

        // Apply basic cleaning first
        $cleaned = $this->cleanValue($name);

        if (null === $cleaned) {
            return null;
        }

        // Remove commas and apply title case
        $cleaned = str_replace(',', '', $cleaned);
        $cleaned = Str::title($cleaned);
        $cleaned = mb_trim($cleaned);

        return '' !== $cleaned ? $cleaned : null;
    }
}
