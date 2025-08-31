<?php

declare(strict_types=1);

namespace App\Console\Commands\Kobo;

use App\Jobs\Kobo\ImportAccessibleLocationsJob;
use App\Jobs\Kobo\ImportNonAccessibleLocationsJob;
use App\Services\Kobo\AccessibleLocationsProcess;
use App\Services\Kobo\AccessibleLocationsService;
use App\Services\Kobo\NonAccessibleLocationsProcess;
use App\Services\Kobo\NonAccessibleLocationsService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class ImportAllLocationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kobo:import-all
                           {--sync : Run synchronously instead of dispatching jobs}
                           {--force : Force update existing records}
                           {--only-accessible : Import only accessible locations}
                           {--only-non-accessible : Import only non-accessible locations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all locations data (both accessible and non-accessible) from KoboToolbox';

    /**
     * Execute the console command.
     */
    public function handle(
        AccessibleLocationsProcess $accessibleProcessor,
        NonAccessibleLocationsProcess $nonAccessibleProcessor,
        AccessibleLocationsService $accessibleService,
        NonAccessibleLocationsService $nonAccessibleService,
    ): int {
        $this->info('Starting comprehensive locations import from KoboToolbox...');

        try {
            $options = [
                'force_update' => $this->option('force'),
            ];

            // Determine which imports to run
            $importAccessible = !$this->option('only-non-accessible');
            $importNonAccessible = !$this->option('only-accessible');

            // Validate Kobo connections before proceeding
            $this->info('Validating Kobo API connections...');

            if ($importAccessible) {
                $this->info('Validating accessible locations connection...');
                $accessibleService->validateConnection();
                $this->info('✓ Accessible locations API connection validated');
            }

            if ($importNonAccessible) {
                $this->info('Validating non-accessible locations connection...');
                $nonAccessibleService->validateConnection();
                $this->info('✓ Non-accessible locations API connection validated');
            }

            $this->info('✓ All Kobo API connections validated successfully');

            if ($this->option('sync')) {
                // Run synchronously
                $this->info('Running synchronously...');
                $totalStats = $this->runSynchronously($accessibleProcessor, $nonAccessibleProcessor, $importAccessible, $importNonAccessible);
                $this->displayCombinedStats($totalStats);
            } else {
                // Dispatch jobs only if connection validation passed
                $this->info('Dispatching import jobs...');
                $this->dispatchJobs($options, $importAccessible, $importNonAccessible);
                $this->info('Import jobs dispatched successfully!');
                $this->comment('Check the logs for progress updates.');
            }

            return self::SUCCESS;

        } catch (Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());

            // Log connection failures specifically
            if (str_contains($e->getMessage(), 'connection') || str_contains($e->getMessage(), 'non-200')) {
                $this->error('✗ Kobo API connection failed - jobs will not be dispatched');
                Log::error('Kobo API connection validation failed - preventing job dispatch', [
                    'command' => 'kobo:import-all',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            } else {
                Log::error('Import all command failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            return self::FAILURE;
        }
    }

    /**
     * Run imports synchronously and combine statistics.
     *
     * @param AccessibleLocationsProcess $accessibleProcessor
     * @param NonAccessibleLocationsProcess $nonAccessibleProcessor
     * @param bool $importAccessible
     * @param bool $importNonAccessible
     * @return array{accessible: array<string, int>, non_accessible: array<string, int>, total: array<string, int>}
     */
    private function runSynchronously(
        AccessibleLocationsProcess $accessibleProcessor,
        NonAccessibleLocationsProcess $nonAccessibleProcessor,
        bool $importAccessible,
        bool $importNonAccessible
    ): array {
        $accessibleStats = [];
        $nonAccessibleStats = [];

        if ($importAccessible) {
            $this->info('Processing accessible locations...');
            $accessibleStats = $accessibleProcessor->processAll();
            $this->line('✓ Accessible locations completed');
        }

        if ($importNonAccessible) {
            $this->info('Processing non-accessible locations...');
            $nonAccessibleStats = $nonAccessibleProcessor->processAll();
            $this->line('✓ Non-accessible locations completed');
        }

        // Calculate totals
        $totalStats = $this->calculateTotalStats($accessibleStats, $nonAccessibleStats);

        return [
            'accessible' => $accessibleStats,
            'non_accessible' => $nonAccessibleStats,
            'total' => $totalStats,
        ];
    }

    /**
     * Dispatch jobs for asynchronous processing.
     */
    private function dispatchJobs(array $options, bool $importAccessible, bool $importNonAccessible): void
    {
        if ($importAccessible) {
            ImportAccessibleLocationsJob::dispatch($options);
            $this->line('✓ Accessible locations job dispatched');
        }

        if ($importNonAccessible) {
            ImportNonAccessibleLocationsJob::dispatch($options);
            $this->line('✓ Non-accessible locations job dispatched');
        }
    }

    /**
     * Calculate combined statistics.
     *
     * @param array<string, int> $accessibleStats
     * @param array<string, int> $nonAccessibleStats
     * @return array<string, int>
     */
    private function calculateTotalStats(array $accessibleStats, array $nonAccessibleStats): array
    {
        $keys = ['processed', 'created', 'updated', 'skipped', 'errors'];
        $totals = [];

        foreach ($keys as $key) {
            $totals[$key] = ($accessibleStats[$key] ?? 0) + ($nonAccessibleStats[$key] ?? 0);
        }

        return $totals;
    }

    /**
     * Display combined import statistics.
     *
     * @param array{accessible: array<string, int>, non_accessible: array<string, int>, total: array<string, int>} $stats
     */
    private function displayCombinedStats(array $stats): void
    {
        $this->newLine();
        $this->info('Combined Import Statistics:');

        // Show breakdown by type
        if ( ! empty($stats['accessible'])) {
            $this->line('Accessible Locations:');
            $this->displayStatsTable($stats['accessible'], '  ');
        }

        if ( ! empty($stats['non_accessible'])) {
            $this->line('Non-Accessible Locations:');
            $this->displayStatsTable($stats['non_accessible'], '  ');
        }

        // Show totals
        $this->newLine();
        $this->info('Total Summary:');
        $this->displayStatsTable($stats['total']);

        if ($stats['total']['errors'] > 0) {
            $this->warn('Some records had errors. Check the logs for details.');
        } else {
            $this->info('All records processed successfully!');
        }
    }

    /**
     * Display statistics in table format.
     *
     * @param array<string, int> $stats
     */
    private function displayStatsTable(array $stats, string $prefix = ''): void
    {
        $rows = [];
        foreach ($stats as $metric => $count) {
            $rows[] = [$prefix . ucfirst($metric), $count];
        }

        $this->table(['Metric', 'Count'], $rows);
    }
}
