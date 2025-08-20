<?php

declare(strict_types=1);

namespace App\Adapter\Kobo;

use App\Enum\Location\Category;
use Illuminate\Support\Str;

final class AccessibleLocationAdapter
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
            'type' => $this->extractLocationType($data),
            'category' => Category::ACCESSIBLE,
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

            // Skip arrays and objects for now (could be enhanced later)
            if (is_array($value)) {
                continue;
            }

            $title = $this->decodeQuestionTitle($key);

            $infos[] = [
                'title' => $title,
                'value' => $this->cleanValue((string) $value),
            ];
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
        return $this->cleanValue($data['_2_Qual_o_nome_desse_lugar_ou_ponto'] ?? null);
    }

    /**
     * Extract location type from Kobo data.
     */
    private function extractLocationType(array $data): ?string
    {
        return $this->cleanValue($data['_3_Que_tipo_de_lugar_esse'] ?? null);
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
            'formhub/uuid',
            'start',
            'end',
            '__version__',
            'meta/instanceID',
            'meta/rootUuid',
            '_2_Qual_o_nome_desse_lugar_ou_ponto', // Mapped to location.name
            '_3_Que_tipo_de_lugar_esse', // Mapped to location.type
            '_10_Nome_dos_dois_pe_aram_este_formul_rio', // Mapped to location.authors
        ];
    }

    /**
     * Decode question title from Kobo field name.
     */
    private function decodeQuestionTitle(string $key): string
    {
        // Basic decoding - replace underscores with spaces and clean up
        $title = str_replace('_', ' ', $key);
        $title = preg_replace('/^\d+\s+/', '', $title); // Remove leading numbers
        $title = Str::title($title);

        // Custom mappings for known question patterns
        $mappings = [
            '_1_Na_sua_perspectiva_este_lu' => 'Na sua perspectiva, este lugar é acessível?',
            '_4_Compartilhe_a_localiza_o' => 'Localização GPS',
            '_5_Quais_elementos_to_trutura_e_mobilidade' => 'Elementos de infraestrutura e mobilidade',
            '_5_1_Quais_elementos_porte_e_deslocamento' => 'Elementos de transporte e deslocamento',
            '_5_2_Quais_elementos_omunica_o_e_intera_o' => 'Elementos de comunicação e interação',
            '_5_3_Quais_s_o_os_ou_este_local_acess_vel' => 'Outros aspectos que tornam este local acessível',
            '_6_O_entorno_imediat_se_local_acess_vel' => 'O entorno imediato deste local é acessível?',
            '_6_1_Se_n_o_for_acess_descreva_o_problema' => 'Descreva o problema de acessibilidade',
            '_7_Tire_uma_foto_que_mostre_o_local' => 'Foto do local',
            '_8_Tire_mais_uma_fot_gora_de_outro_ngulo' => 'Segunda foto do local',
            '_9_Grave_um_udio_ex_esse_lugar_acess_vel' => 'Áudio explicativo',
        ];

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

        // Clean up common Kobo encoding issues
        $cleaned = str_replace('_', ' ', $value);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        $cleaned = mb_trim($cleaned);

        return '' !== $cleaned ? $cleaned : null;
    }
}
