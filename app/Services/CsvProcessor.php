<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LocationType;
use App\Models\Location;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class CsvProcessor
{
    private array $errors = [];
    private int $processedCount = 0;
    private int $skippedCount = 0;

    public function process(UploadedFile|string $file): array
    {
        $this->resetCounters();

        if ( ! $this->isValidCsvFile($file)) {
            return $this->getResult('Arquivo CSV inválido.');
        }

        $csvData = $this->parseCsvFile($file);

        if (empty($csvData)) {
            return $this->getResult('Arquivo CSV vazio ou formato inválido.');
        }

        return $this->processRecords($csvData);
    }

    public function getExpectedHeaders(): array
    {
        return [
            'type' => 'Tipo (accessible, partially_accessible, not_accessible)',
            'name' => 'Nome do local',
            'address' => 'Endereço',
            'description' => 'Descrição (opcional)',
            'latitude' => 'Latitude (-90 a 90)',
            'longitude' => 'Longitude (-180 a 180)',
            'published' => 'Publicado (opcional: 1, true, sim, yes)',
        ];
    }

    public function generateSampleCsv(): string
    {
        $headers = array_keys($this->getExpectedHeaders());
        $sampleData = [
            ['accessible', 'Praça da Paz', 'Rua das Flores, 123', 'Praça com rampa de acesso', '-22.8765', '-43.2345', '1'],
            ['partially_accessible', 'Centro Comunitário', 'Av. Principal, 456', 'Acesso limitado no segundo andar', '-22.8766', '-43.2346', '0'],
            ['not_accessible', 'Ponte Velha', 'Rua da Ponte, s/n', 'Sem acesso para cadeirantes', '-22.8767', '-43.2347', '1'],
        ];

        $csv = implode(',', $headers) . "\n";
        foreach ($sampleData as $row) {
            $csv .= implode(',', array_map(fn($field) => '"' . str_replace('"', '""', $field) . '"', $row)) . "\n";
        }

        return $csv;
    }

    private function isValidCsvFile(UploadedFile|string $file): bool
    {
        if ($file instanceof UploadedFile) {
            if ( ! $file->isValid()) {
                return false;
            }

            $allowedExtensions = ['csv'];
            $allowedMimeTypes = [
                'text/csv',
                'text/plain',
                'application/csv',
                'application/vnd.ms-excel',
            ];

            return in_array(mb_strtolower((string) $file->getClientOriginalExtension()), $allowedExtensions, true)
                && in_array((string) $file->getMimeType(), $allowedMimeTypes, true);
        }

        // File path validation
        return file_exists($file) && is_readable($file) && str_ends_with(strtolower($file), '.csv');
    }

    private function parseCsvFile(UploadedFile|string $file): array
    {
        $csvData = [];
        $filePath = $file instanceof UploadedFile ? $file->getPathname() : $file;
        $handle = fopen($filePath, 'r');

        if (false === $handle) {
            return [];
        }

        // Read header row
        $headers = fgetcsv($handle);
        if ( ! $headers || ! $this->validateHeaders($headers)) {
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

    private function validateHeaders(array $headers): bool
    {
        $requiredHeaders = ['type', 'name', 'address', 'latitude', 'longitude'];
        $normalizedHeaders = array_map('strtolower', array_map('trim', $headers));

        foreach ($requiredHeaders as $required) {
            if ( ! in_array($required, $normalizedHeaders)) {
                $this->errors[] = "Cabeçalho obrigatório '{$required}' não encontrado.";
                return false;
            }
        }

        return true;
    }

    private function processRecords(array $csvData): array
    {
        DB::beginTransaction();

        try {
            foreach ($csvData as $index => $row) {
                $this->processRecord($row, $index + 2); // +2 for header and 1-based indexing
            }

            DB::commit();
            return $this->getResult();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->getResult('Erro durante o processamento: ' . $e->getMessage());
        }
    }

    private function processRecord(array $row, int $lineNumber): void
    {
        $data = $this->normalizeRowData($row);
        $validator = $this->validateRecord($data, $lineNumber);

        if ($validator->fails()) {
            $this->skippedCount++;
            $this->errors[] = "Linha {$lineNumber}: " . implode(', ', $validator->errors()->all());
            return;
        }

        try {
            Location::create([
                'type' => $data['type'],
                'name' => $data['name'],
                'address' => $data['address'],
                'description' => $data['description'] ?? null,
                'latitude' => (float) $data['latitude'],
                'longitude' => (float) $data['longitude'],
                'published_at' => ($data['published'] ?? false) ? now() : null,
            ]);

            $this->processedCount++;
        } catch (Exception $e) {
            $this->skippedCount++;
            $this->errors[] = "Linha {$lineNumber}: Erro ao salvar - " . $e->getMessage();
        }
    }

    private function normalizeRowData(array $row): array
    {
        return [
            'type' => mb_trim(mb_strtolower($row['type'] ?? '')),
            'name' => mb_trim($row['name'] ?? ''),
            'address' => mb_trim($row['address'] ?? ''),
            'description' => mb_trim($row['description'] ?? ''),
            'latitude' => mb_trim($row['latitude'] ?? ''),
            'longitude' => mb_trim($row['longitude'] ?? ''),
            'published' => in_array(mb_strtolower(mb_trim($row['published'] ?? '')), ['1', 'true', 'sim', 'yes']),
        ];
    }

    private function validateRecord(array $data, int $lineNumber): \Illuminate\Validation\Validator
    {
        return Validator::make($data, [
            'type' => ['required', Rule::enum(LocationType::class)],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'published' => ['boolean'],
        ], [
            'type.required' => 'Tipo é obrigatório',
            'type.enum' => 'Tipo deve ser: accessible, partially_accessible ou not_accessible',
            'name.required' => 'Nome é obrigatório',
            'name.max' => 'Nome não pode ter mais de 255 caracteres',
            'address.required' => 'Endereço é obrigatório',
            'latitude.required' => 'Latitude é obrigatória',
            'latitude.numeric' => 'Latitude deve ser um número',
            'latitude.between' => 'Latitude deve estar entre -90 e 90',
            'longitude.required' => 'Longitude é obrigatória',
            'longitude.numeric' => 'Longitude deve ser um número',
            'longitude.between' => 'Longitude deve estar entre -180 e 180',
        ]);
    }

    private function resetCounters(): void
    {
        $this->errors = [];
        $this->processedCount = 0;
        $this->skippedCount = 0;
    }

    private function getResult(?string $error = null): array
    {
        return [
            'success' => null === $error,
            'message' => $error ?? $this->getSuccessMessage(),
            'processed' => $this->processedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errors,
        ];
    }

    private function getSuccessMessage(): string
    {
        $message = "Processamento concluído. {$this->processedCount} registros importados";

        if ($this->skippedCount > 0) {
            $message .= ", {$this->skippedCount} registros ignorados";
        }

        return $message . '.';
    }
}
