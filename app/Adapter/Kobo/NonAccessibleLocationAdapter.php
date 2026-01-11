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
     * @return array{location: array<string, mixed>, images: array<int, array<string, mixed>>, infos: array<int, array<string, mixed>>}|null
     */
    public function transform(array $koboData): ?array
    {
        if ( ! $this->isValidPerspectiveQuestion($koboData)) {
            return null;
        }

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
            'published_at' => null,
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

            if ($this->isImageMimeType($attachment['mimetype'] ?? '')) {
                $images[] = [
                    'image_path' => null,
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
            if (in_array($key, $excludedKeys) || null === $value || '' === $value) {
                continue;
            }

            if (is_array($value)) {
                continue;
            }

            if ($this->isMediaFile((string) $value)) {
                continue;
            }

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
     * Extract location description from Kobo data.
     */
    private function extractLocationDescription(array $data): string
    {
        $rawType = $data['_3_Que_tipo_de_lugar_esse'] ?? null;
        $cleanedValue = $this->cleanValue($rawType);

        return $cleanedValue ?? 'Local não acessível';
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
            '_2_Qual_o_nome_desse_lugar_ou_ponto',
            '_3_Que_tipo_de_lugar_esse',
            '_10_Nome_dos_dois_pe_aram_este_formul_rio',
            '_1_Na_sua_perspectiva_este_lu',
            '_4_Compartilhe_a_localiza_o',
            '_6_Compartilhe_a_localiza_o',
            '_4_Compartilhe_a_localiza_o_001',
            '_7_Tire_uma_foto_que_mostre_o_local',
            '_8_Tire_mais_uma_fot_gora_de_outro_ngulo',
            '_9_Grave_um_udio_ex_ugar_n_o_acess_vel',
        ];
    }

    /**
     * Decode question title from Kobo field name.
     */
    private function decodeQuestionTitle(string $key): string
    {
        $title = str_replace('_', ' ', $key);
        $title = preg_replace('/^\d+\s+/', '', $title);
        $title = Str::title($title);

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

        $decoded = $this->decodeValueFromConfig($value);
        if ($decoded !== $value) {
            return $decoded;
        }

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

        if (isset($mappings[$value])) {
            return $mappings[$value];
        }

        if (str_contains($value, ' ')) {
            $parts = explode(' ', $value);
            $decodedParts = [];

            foreach ($parts as $part) {
                if ('' !== mb_trim($part)) {
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
        $mediaPatterns = [
            '/\.(jpg|jpeg|png|gif|webp|heic)$/i',
            '/\.(mp3|m4a|wav|aac|opus|mov)$/i',
            '/^\d+.*\.(jpg|jpeg|png|gif|webp|heic|mp3|m4a|wav|aac|opus|mov)$/i',
            '/^[A-Za-z]+.*\d+.*\.(jpg|jpeg|png|gif|webp|heic|mp3|m4a|wav|aac|opus|mov)$/i',
            '/^image-\d+/i',
            '/^IMG_\d+/i',
            '/Voz\s+\d+/i',
            '/C:\\\\fakepath\\\\/i',
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
        $coordinatePatterns = [
            '/^-?\d+\.\d+\s+-?\d+\.\d+/',
            '/^-?\d+\.\d+\s+-?\d+\.\d+\s+[\d\.\-]+/',
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

        $cleaned = $this->cleanValue($name);

        if (null === $cleaned) {
            return null;
        }

        $cleaned = str_replace(',', '', $cleaned);
        $cleaned = Str::title($cleaned);
        $cleaned = mb_trim($cleaned);

        return '' !== $cleaned ? $cleaned : null;
    }

    /**
     * Validate if the perspective question has a value.
     */
    private function isValidPerspectiveQuestion(array $data): bool
    {
        $perspectiveValue = $data['_1_Na_sua_perspectiva_este_lu'] ?? null;

        return null !== $perspectiveValue && '' !== mb_trim((string) $perspectiveValue);
    }
}
