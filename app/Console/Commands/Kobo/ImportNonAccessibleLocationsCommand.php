<?php

declare(strict_types=1);

namespace App\Console\Commands\Kobo;

use App\Jobs\Kobo\ImportNonAccessibleLocationsJob;
use App\Services\Kobo\NonAccessibleLocationsProcess;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class ImportNonAccessibleLocationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kobo:import-non-accessible 
                           {--sync : Run synchronously instead of dispatching a job}
                           {--force : Force update existing records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import non-accessible locations data from KoboToolbox';

    /**
     * Execute the console command.
     */
    public function handle(NonAccessibleLocationsProcess $processor): int
    {
        $this->info('Starting non-accessible locations import from KoboToolbox...');

        try {
            $options = [
                'force_update' => $this->option('force'),
            ];

            if ($this->option('sync')) {
                // Run synchronously
                $this->info('Running synchronously...');
                $stats = $processor->processAll();
                $this->displayStats($stats);
            } else {
                // Dispatch job
                $this->info('Dispatching import job...');
                ImportNonAccessibleLocationsJob::dispatch($options);
                $this->info('Import job dispatched successfully!');
                $this->comment('Check the logs for progress updates.');
            }

            return self::SUCCESS;

        } catch (Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            Log::error('Non-accessible import command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Display import statistics.
     *
     * @param array{processed: int, created: int, updated: int, skipped: int, errors: int} $stats
     */
    private function displayStats(array $stats): void
    {
        $this->newLine();
        $this->info('Import Statistics:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Processed', $stats['processed']],
                ['Created', $stats['created']],
                ['Updated', $stats['updated']],
                ['Skipped', $stats['skipped']],
                ['Errors', $stats['errors']],
            ],
        );

        if ($stats['errors'] > 0) {
            $this->warn('Some records had errors. Check the logs for details.');
        } else {
            $this->info('All records processed successfully!');
        }
    }
}
