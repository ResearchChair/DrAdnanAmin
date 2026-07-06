<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class PublicStorage
{
    public static function disk()
    {
        return Storage::disk('public');
    }

    public static function exists(?string $path): bool
    {
        return filled($path) && static::disk()->exists($path);
    }

    public static function url(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (static::symlinkWorks()) {
            return asset('storage/'.ltrim($path, '/'));
        }

        return route('storage.serve', ['path' => ltrim($path, '/')]);
    }

    public static function symlinkWorks(): bool
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (! file_exists($link)) {
            return false;
        }

        if (is_link($link)) {
            return realpath($link) === realpath($target);
        }

        return is_dir($link);
    }

    public static function ensureDirectories(): void
    {
        foreach (['profile', 'gallery', 'products', 'students', 'livewire-tmp', 'cv'] as $directory) {
            static::disk()->makeDirectory($directory);
        }
    }

    /**
     * @return list<string>
     */
    public static function writableChecks(): array
    {
        $issues = [];

        foreach ([
            storage_path('app/public'),
            storage_path('app/public/profile'),
            storage_path('app/public/students'),
            storage_path('app/public/livewire-tmp'),
            storage_path('framework/cache'),
            storage_path('logs'),
        ] as $path) {
            if (! is_dir($path)) {
                $issues[] = "Missing directory: {$path}";

                continue;
            }

            if (! is_writable($path)) {
                $issues[] = "Not writable: {$path}";
            }
        }

        if (! static::symlinkWorks() && ! app()->routesAreCached()) {
            $issues[] = 'Storage symlink missing — run: php artisan storage:link (fallback route will serve files until then)';
        }

        return $issues;
    }
}
