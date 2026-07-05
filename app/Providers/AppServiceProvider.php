<?php

namespace App\Providers;

use App\Models\Profile;
use App\Observers\ProfileObserver;
use App\Services\AdminUserSync;
use App\Support\PublicStorage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        config([
            'livewire.temporary_file_upload.disk' => 'public',
        ]);

        PublicStorage::ensureDirectories();
        $this->registerStorageFallbackRoute();

        if (Schema::hasTable('users')) {
            AdminUserSync::sync();
        }

        Profile::observe(ProfileObserver::class);
    }

    protected function registerStorageFallbackRoute(): void
    {
        if (PublicStorage::symlinkWorks()) {
            return;
        }

        Route::get('/storage/{path}', function (string $path) {
            abort_unless(Storage::disk('public')->exists($path), 404);

            return Storage::disk('public')->response($path);
        })->where('path', '.*')->name('storage.serve');
    }
}
