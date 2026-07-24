<?php

use App\Http\Controllers\CvDownloadController;
use App\Http\Controllers\PhotoDownloadController;
use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortfolioController::class, 'home'])->name('home');
Route::get('/about', [PortfolioController::class, 'about'])->name('about');
Route::get('/publications', [PortfolioController::class, 'publications'])->name('publications');
Route::get('/publications/collaborator/{email}', [PortfolioController::class, 'collaboratorPublications'])
    ->middleware(['signed', 'throttle:15,1'])
    ->name('publications.collaborator');
Route::get('/research', [PortfolioController::class, 'research'])->name('research');
Route::get('/students', [PortfolioController::class, 'students'])->name('students');
Route::get('/training', [PortfolioController::class, 'training'])->name('training');
Route::get('/services', [PortfolioController::class, 'services'])->name('services');
Route::get('/gallery', [PortfolioController::class, 'gallery'])->name('gallery');
Route::get('/contact', [PortfolioController::class, 'contact'])->name('contact');
Route::get('/cv', [CvDownloadController::class, 'show'])->name('cv.show');
Route::post('/cv/download', [CvDownloadController::class, 'download'])->name('cv.download');
Route::get('/photo/download', PhotoDownloadController::class)->name('photo.download');

Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin',
        'Disallow: /livewire',
        '',
        'Sitemap: '.url('/sitemap.xml'),
        '',
    ];

    return response(implode("\n", $lines), 200, [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
})->name('robots');

Route::get('/sitemap.xml', function () {
    $urls = [
        ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'weekly'],
        ['loc' => route('about'), 'priority' => '0.9', 'changefreq' => 'monthly'],
        ['loc' => route('publications'), 'priority' => '0.9', 'changefreq' => 'weekly'],
        ['loc' => route('research'), 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => route('students'), 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => route('training'), 'priority' => '0.7', 'changefreq' => 'monthly'],
        ['loc' => route('services'), 'priority' => '0.7', 'changefreq' => 'monthly'],
        ['loc' => route('gallery'), 'priority' => '0.6', 'changefreq' => 'monthly'],
        ['loc' => route('contact'), 'priority' => '0.7', 'changefreq' => 'monthly'],
    ];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    foreach ($urls as $entry) {
        $xml .= '<url>';
        $xml .= '<loc>'.e($entry['loc']).'</loc>';
        $xml .= '<changefreq>'.$entry['changefreq'].'</changefreq>';
        $xml .= '<priority>'.$entry['priority'].'</priority>';
        $xml .= '<lastmod>'.now()->toDateString().'</lastmod>';
        $xml .= '</url>';
    }

    return response($xml.'</urlset>', 200, [
        'Content-Type' => 'application/xml; charset=UTF-8',
    ]);
})->name('sitemap');
