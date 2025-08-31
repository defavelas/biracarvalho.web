<?php

declare(strict_types=1);

namespace App\Jobs\Kobo;

use App\Services\Kobo\AccessibleLocationsProcess;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ImportAccessibleLocationsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 300; // 5 minutes

    public function __construct(
        private readonly array $options = [],
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AccessibleLocationsProcess $processor): void
    {
        Log::info('Starting accessible locations import job', $this->options);

        try {
            $stats = $processor->processAll();

            Log::info('Accessible locations import job completed successfully', $stats);

        } catch (Exception $e) {
            // Log connection failures specifically
            if (str_contains($e->getMessage(), 'connection') || str_contains($e->getMessage(), 'non-200')) {
                Log::error('Accessible locations import job failed due to Kobo API connection error', [
                    'error' => $e->getMessage(),
                    'options' => $this->options,
                    'job' => 'ImportAccessibleLocationsJob',
                    'failure_reason' => 'kobo_connection_failed',
                ]);
            } else {
                Log::error('Accessible locations import job failed', [
                    'error' => $e->getMessage(),
                    'options' => $this->options,
                    'job' => 'ImportAccessibleLocationsJob',
                ]);
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        // Log permanent failures with more context
        if (str_contains($exception->getMessage(), 'connection') || str_contains($exception->getMessage(), 'non-200')) {
            Log::error('Accessible locations import job failed permanently due to Kobo API connection error', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'options' => $this->options,
                'job' => 'ImportAccessibleLocationsJob',
                'failure_reason' => 'kobo_connection_failed',
                'retries_exhausted' => true,
            ]);
        } else {
            Log::error('Accessible locations import job failed permanently', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'options' => $this->options,
                'job' => 'ImportAccessibleLocationsJob',
                'retries_exhausted' => true,
            ]);
        }
    }
}
