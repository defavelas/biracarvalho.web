<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ImportStatus;
use App\Models\ImportJob;
use App\Services\CsvProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ProcessCsvImport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public string $filePath;
    public string $originalName;
    public string $importJobId;

    public function __construct(string $filePath, string $originalName, string $importJobId)
    {
        $this->filePath = $filePath;
        $this->originalName = $originalName;
        $this->importJobId = $importJobId;
    }

    public function handle(): void
    {
        /** @var ImportJob $importJob */
        $importJob = ImportJob::findOrFail($this->importJobId);
        $importJob->update([
            'status' => ImportStatus::Processing,
            'started_at' => now(),
        ]);

        try {
            $processor = new CsvProcessor();
            $result = $processor->process(new \Illuminate\Http\UploadedFile(
                path: storage_path('app/' . $this->filePath),
                originalName: $this->originalName,
                test: true,
            ));

            $importJob->update([
                'status' => $result['success'] ? ImportStatus::Completed : ImportStatus::Failed,
                'processed_rows' => $result['processed'] ?? 0,
                'skipped_rows' => $result['skipped'] ?? 0,
                'error_message' => $result['success'] ? null : ($result['message'] ?? null),
                'finished_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('CSV import failed', ['error' => $e->getMessage()]);
            $importJob->update([
                'status' => ImportStatus::Failed,
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);
        }
    }
}
