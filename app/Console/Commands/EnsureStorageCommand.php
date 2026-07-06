<?php

namespace App\Console\Commands;

use App\Support\PublicStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class EnsureStorageCommand extends Command
{
    protected $signature = 'portfolio:ensure-storage {--link : Create the public/storage symlink}';

    protected $description = 'Prepare storage directories and symlink for public file uploads (profile photo, gallery, etc.)';

    public function handle(): int
    {
        PublicStorage::ensureDirectories();
        $this->info('Created public storage directories (profile, gallery, students, products, livewire-tmp).');

        if ($this->option('link') || ! PublicStorage::symlinkWorks()) {
            if (PublicStorage::symlinkWorks()) {
                $this->info('Storage symlink already exists.');
            } else {
                Artisan::call('storage:link');
                $this->line(Artisan::output());

                if (! PublicStorage::symlinkWorks()) {
                    $this->warn('Could not create storage symlink (common on shared hosting).');
                    $this->warn('The app will serve files via /storage/{path} fallback route instead.');
                }
            }
        }

        $issues = PublicStorage::writableChecks();

        if ($issues === []) {
            $this->info('All storage checks passed.');

            return self::SUCCESS;
        }

        $this->warn('Storage issues found:');
        foreach ($issues as $issue) {
            $this->line("  - {$issue}");
        }

        $this->newLine();
        $this->line('On production, ensure the web server user can write to storage/ and bootstrap/cache/.');
        $this->line('Set FILESYSTEM_DISK=public and APP_URL to your live domain (with https).');

        return self::FAILURE;
    }
}
