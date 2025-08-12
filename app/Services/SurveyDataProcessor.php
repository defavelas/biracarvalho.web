<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LocationType;
use App\Models\CsvImport;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SurveyDataProcessor
{
    protected array $errors = [];
    protected int $processedCount = 0;
    protected int $successfulCount = 0;
    protected int $failedCount = 0;
    protected ?CsvImport $import = null;

    public function processFile(string $filePath, CsvImport $import): array
    {
        $this->import = $import;
        $this->resetCounters();

        if (!$this->isValidCsvFile($filePath)) {
            return $this->getResult('Arquivo CSV inválido.');
        }

        $csvData = $this->parseCsvFile($filePath);

        if (empty($csvData)) {
            return $this->getResult('Arquivo CSV vazio ou formato inválido.');
        }

        // Update total rows count
        $this->import->update(['total_rows' => count($csvData)]);

        return $this->processRecords($csvData);
    }

    protected function isValidCsvFile(string $filePath): bool
    {
        return file_exists($filePath) && is_readable($filePath);
    }

    protected function parseCsvFile(string $filePath): array
    {
        $csvData = [];
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            return [];
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return [];
        }

        // Read data rows
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $csvData[] = array_combine($headers, $row);
            }
        }

        fclose($handle);
        return $csvData;
    }

    protected function processRecords(array $csvData): array
    {
        DB::beginTransaction();

        try {
            foreach ($csvData as $index => $row) {
                $this->processRecord($row, $index + 2); // +2 for header and 1-based indexing
                
                // Update progress every 10 records
                if ($this->processedCount % 10 === 0) {
                    $this->updateProgress();
                }
            }

            // Final progress update
            $this->updateProgress();

            DB::commit();
            return $this->getResult();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->getResult('Erro durante o processamento: ' . $e->getMessage());
        }
    }

    protected function processRecord(array $row, int $lineNumber): void
    {
        try {
            $locationData = $this->extractLocationData($row);
            
            if (!$this->validateLocationData($locationData, $lineNumber)) {
                $this->failedCount++;
                $this->processedCount++;
                return;
            }

            $location = $this->createLocation($locationData);
            $this->processImages($location, $row);

            $this->successfulCount++;
        } catch (\Exception $e) {
            $this->failedCount++;
            $this->errors[] = "Linha {$lineNumber}: Erro ao processar - " . $e->getMessage();
        }

        $this->processedCount++;
    }

    protected function extractLocationData(array $row): array
    {
        // Determine accessibility type
        $accessibility = $row['1. Na sua perspectiva, este lugar é acessível?'] ?? '';
        $type = $this->determineLocationType($accessibility);

        // Extract basic location info
        $name = $row['2. Qual o nome desse lugar ou ponto?'] ?? 
                $row['5. Qual o nome desse lugar ou ponto?'] ?? 
                'Local sem nome';

        $latitude = $this->extractCoordinate($row, 'latitude');
        $longitude = $this->extractCoordinate($row, 'longitude');

        // Compose description from question responses
        $description = $this->composeDescription($row, $type);

        return [
            'type' => $type,
            'name' => trim($name),
            'address' => $this->extractAddress($row),
            'description' => $description,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'published_at' => now(),
        ];
    }

    protected function determineLocationType(string $accessibility): LocationType
    {
        if (str_contains(strtolower($accessibility), 'sim') || 
            str_contains(strtolower($accessibility), 'acessível')) {
            return LocationType::ACCESSIBLE;
        }

        if (str_contains(strtolower($accessibility), 'potencial')) {
            return LocationType::PARTIALLY_ACCESSIBLE;
        }

        return LocationType::NOT_ACCESSIBLE;
    }

    protected function extractCoordinate(array $row, string $type): float
    {
        // Try different column name patterns
        $patterns = [
            "_4. Compartilhe a localização _{$type}",
            "_6. Compartilhe a localização _{$type}",
            "4. Compartilhe a localização_{$type}",
            "6. Compartilhe a localização_{$type}",
        ];

        foreach ($patterns as $pattern) {
            if (isset($row[$pattern]) && is_numeric($row[$pattern])) {
                return (float) $row[$pattern];
            }
        }

        return 0.0;
    }

    protected function extractAddress(array $row): string
    {
        // Try to extract place type information
        $placeTypes = [];
        
        $typeColumns = [
            '3. Que tipo de lugar é esse?/Casa ou prédio residencial',
            '3. Que tipo de lugar é esse?/Rua, calçada ou passagem',
            '3. Que tipo de lugar é esse?/Praça, área de lazer ou espaço público',
            '3. Que tipo de lugar é esse?/Pontos de ônibus e outros transportes',
            '3. Que tipo de lugar é esse?/Escolas, unidades de saúde ou outros serviços públicos',
            '3. Que tipo de lugar é esse?/Espaços de cultura ou comunitários',
            '3. Que tipo de lugar é esse?/Comércio ou serviço (mercado, farmácia, restaurante, etc.)',
            '4. Que tipo de lugar é esse?/Casa ou prédio residencial',
            '4. Que tipo de lugar é esse?/Rua, calçada ou passagem',
            '4. Que tipo de lugar é esse?/Praça, área de lazer ou espaço público',
            '4. Que tipo de lugar é esse?/Pontos de ônibus e outros transportes',
            '4. Que tipo de lugar é esse?/Escolas, unidades de saúde ou outros serviços públicos',
            '4. Que tipo de lugar é esse?/Espaços de cultura ou comunitários',
            '4. Que tipo de lugar é esse?/Comércio ou serviço (mercado, farmácia, restaurante, etc.)',
        ];

        foreach ($typeColumns as $column) {
            if (isset($row[$column]) && $row[$column] === '1') {
                $placeTypes[] = str_replace(['3. Que tipo de lugar é esse?/', '4. Que tipo de lugar é esse?/'], '', $column);
            }
        }

        return implode(', ', $placeTypes) ?: 'Endereço não especificado';
    }

    protected function composeDescription(array $row, LocationType $type): string
    {
        $description = [];

        // Add accessibility assessment
        $accessibility = $row['1. Na sua perspectiva, este lugar é acessível?'] ?? '';
        if ($accessibility) {
            $description[] = "Avaliação de acessibilidade: {$accessibility}";
        }

        if ($type === LocationType::ACCESSIBLE) {
            $description = array_merge($description, $this->extractAccessibilityFeatures($row));
        } else {
            $description = array_merge($description, $this->extractAccessibilityBarriers($row));
        }

        // Add environment assessment
        $environment = $row['6. O entorno imediato desse local é acessível?'] ?? 
                      $row['3. O entorno imediato desse local é acessível?'] ?? '';
        if ($environment) {
            $description[] = "Entorno: {$environment}";
        }

        // Add additional comments
        $comments = $row['6.1 Se não for acessível, descreva o problema:'] ?? 
                   $row['3.1 Se não for acessível, descreva o problema:'] ?? '';
        if ($comments) {
            $description[] = "Observações: {$comments}";
        }

        // Add researchers info
        $researchers = $row['10. Nome dos dois pesquisadores que aplicaram este formulário'] ?? '';
        if ($researchers) {
            $description[] = "Pesquisadores: {$researchers}";
        }

        return implode("\n\n", array_filter($description));
    }

    protected function extractAccessibilityFeatures(array $row): array
    {
        $features = [];

        // Infrastructure features
        $infraFeatures = $this->extractSelectedOptions($row, [
            '5.Quais elementos tornam esse lugar acessível do ponto de vista da infraestrutura e mobilidade?',
            '2.Quais elementos tornam esse lugar acessível do ponto de vista da infraestrutura e mobilidade?',
            '2.1. Do ponto de vista da infraestrutura e mobilidade?'
        ]);

        if (!empty($infraFeatures)) {
            $features[] = "Infraestrutura e mobilidade: " . implode(', ', $infraFeatures);
        }

        // Transport features
        $transportFeatures = $this->extractSelectedOptions($row, [
            '5.1 Quais elementos tornam esse lugar acessível do ponto de vista do transporte e deslocamento',
            '2.2. Quais elementos tornam esse lugar acessível do ponto de vista do transporte e deslocamento',
            '2.1 Quais elementos tornam esse lugar acessível do ponto de vista do transporte e deslocamento'
        ]);

        if (!empty($transportFeatures)) {
            $features[] = "Transporte e deslocamento: " . implode(', ', $transportFeatures);
        }

        // Communication features
        $commFeatures = $this->extractSelectedOptions($row, [
            '5.2 Quais elementos tornam esse lugar acessível do ponto de vista da comunicação e interação?',
            '2.3 Comunicação e interação',
            '2.2 Quais elementos tornam esse lugar acessível do ponto de vista da comunicação e interação?'
        ]);

        if (!empty($commFeatures)) {
            $features[] = "Comunicação e interação: " . implode(', ', $commFeatures);
        }

        return $features;
    }

    protected function extractAccessibilityBarriers(array $row): array
    {
        $barriers = [];

        // Infrastructure barriers
        $infraBarriers = $this->extractSelectedOptions($row, [
            '5. Quais barreiras de acessibilidade você identificou nesse local do ponto de vista da infraestrutura e mobilidade?',
            '2. Quais barreiras de acessibilidade você identificou nesse local do ponto de vista da infraestrutura e mobilidade?',
            '2.1. Do ponto de vista da infraestrutura e mobilidade?'
        ]);

        if (!empty($infraBarriers)) {
            $barriers[] = "Barreiras de infraestrutura: " . implode(', ', $infraBarriers);
        }

        // Transport barriers
        $transportBarriers = $this->extractSelectedOptions($row, [
            '5.1. Quais barreiras de acessibilidade você identificou nesse local do ponto de vista do transporte e deslocamento?',
            '2.1. Quais barreiras de acessibilidade você identificou nesse local do ponto de vista do transporte e deslocamento?',
            '2.2. do ponto de vista do transporte e deslocamento?'
        ]);

        if (!empty($transportBarriers)) {
            $barriers[] = "Barreiras de transporte: " . implode(', ', $transportBarriers);
        }

        // Communication barriers
        $commBarriers = $this->extractSelectedOptions($row, [
            '5.2 Quais barreiras de acessibilidade você identificou nesse local do ponto de vista da Comunicação e interação?',
            '2.2 Quais barreiras de acessibilidade você identificou nesse local do ponto de vista da Comunicação e interação?',
            '2.3 Comunicação e interação'
        ]);

        if (!empty($commBarriers)) {
            $barriers[] = "Barreiras de comunicação: " . implode(', ', $commBarriers);
        }

        return $barriers;
    }

    protected function extractSelectedOptions(array $row, array $baseColumns): array
    {
        $selected = [];

        foreach ($baseColumns as $baseColumn) {
            // Look for the main column with selected options
            if (isset($row[$baseColumn]) && !empty($row[$baseColumn])) {
                $selected[] = $row[$baseColumn];
            }

            // Look for individual option columns
            foreach ($row as $column => $value) {
                if (str_starts_with($column, $baseColumn . '/') && $value === '1') {
                    $option = str_replace($baseColumn . '/', '', $column);
                    $selected[] = $option;
                }
            }
        }

        return array_unique($selected);
    }

    protected function validateLocationData(array $data, int $lineNumber): bool
    {
        if (empty($data['name'])) {
            $this->errors[] = "Linha {$lineNumber}: Nome do local é obrigatório";
            return false;
        }

        if ($data['latitude'] === 0.0 || $data['longitude'] === 0.0) {
            $this->errors[] = "Linha {$lineNumber}: Coordenadas inválidas";
            return false;
        }

        return true;
    }

    protected function createLocation(array $data): Location
    {
        return Location::create($data);
    }

    protected function processImages(Location $location, array $row): void
    {
        $imageColumns = [
            '7. Tire uma foto que mostre o local _URL',
            '8. Tire mais uma foto do local, agora de outro ângulo_URL',
            '7. Tire ao menos 02 fotos que mostram o local _URL',
        ];

        foreach ($imageColumns as $column) {
            if (isset($row[$column]) && !empty($row[$column])) {
                $this->downloadAndSaveImage($location, $row[$column]);
            }
        }
    }

    protected function downloadAndSaveImage(Location $location, string $url): void
    {
        try {
            $response = Http::timeout(30)->get($url);
            
            if ($response->successful()) {
                $filename = 'locations/' . $location->id . '/' . Str::random(10) . '.jpg';
                Storage::put($filename, $response->body());

                $location->images()->create([
                    'image_path' => $filename,
                    'published_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't fail the import
            $this->errors[] = "Erro ao baixar imagem: {$e->getMessage()}";
        }
    }

    protected function updateProgress(): void
    {
        if ($this->import) {
            $this->import->updateProgress(
                $this->processedCount,
                $this->successfulCount,
                $this->failedCount,
                $this->errors
            );
        }
    }

    protected function resetCounters(): void
    {
        $this->errors = [];
        $this->processedCount = 0;
        $this->successfulCount = 0;
        $this->failedCount = 0;
    }

    protected function getResult(string $error = null): array
    {
        return [
            'success' => $error === null,
            'message' => $error ?? $this->getSuccessMessage(),
            'processed' => $this->processedCount,
            'successful' => $this->successfulCount,
            'failed' => $this->failedCount,
            'errors' => $this->errors,
        ];
    }

    protected function getSuccessMessage(): string
    {
        $message = "Processamento concluído. {$this->successfulCount} registros importados";
        
        if ($this->failedCount > 0) {
            $message .= ", {$this->failedCount} registros falharam";
        }
        
        return $message . '.';
    }
}

