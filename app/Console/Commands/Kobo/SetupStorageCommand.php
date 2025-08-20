<?php

declare(strict_types=1);

namespace App\Console\Commands\Kobo;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class SetupStorageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kobo:setup-storage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup storage directories and symlinks for Kobo image storage';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            // Create public images directory if it doesn't exist
            $publicImagesPath = storage_path('app/public/images/locations');
            
            if (!File::exists($publicImagesPath)) {
                File::makeDirectory($publicImagesPath, 0755, true);
                $this->info('✓ Created public images directory: ' . $publicImagesPath);
            } else {
                $this->info('✓ Public images directory already exists');
            }

            // Create storage link if it doesn't exist
            $linkPath = public_path('storage');
            
            if (!File::exists($linkPath)) {
                $this->call('storage:link');
                $this->info('✓ Storage symlink created');
            } else {
                $this->info('✓ Storage symlink already exists');
            }

            // Test public access
            $testUrl = asset('storage/images/locations/');
            $this->newLine();
            $this->info('🎯 Images will be accessible at: ' . $testUrl);
            $this->comment('Example URL: ' . $testUrl . 'uuid.jpg');
            
            $this->newLine();
            $this->info('✅ Kobo storage setup completed successfully!');
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Setup failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
