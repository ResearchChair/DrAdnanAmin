<?php

use App\Http\Controllers\CvDownloadController;
use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortfolioController::class, 'home'])->name('home');
Route::get('/about', [PortfolioController::class, 'about'])->name('about');
Route::get('/publications', [PortfolioController::class, 'publications'])->name('publications');
Route::get('/research', [PortfolioController::class, 'research'])->name('research');
Route::get('/students', [PortfolioController::class, 'students'])->name('students');
Route::get('/training', [PortfolioController::class, 'training'])->name('training');
Route::get('/gallery', [PortfolioController::class, 'gallery'])->name('gallery');
Route::get('/contact', [PortfolioController::class, 'contact'])->name('contact');
Route::get('/cv', [CvDownloadController::class, 'show'])->name('cv.show');
Route::post('/cv/download', [CvDownloadController::class, 'download'])->name('cv.download');

Route::get('/sitemap.xml', function () {
    $urls = ['/', '/about', '/publications', '/research', '/students', '/training', '/gallery', '/contact', '/cv'];
    $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    foreach ($urls as $url) {
        $xml .= '<url><loc>'.e(url($url)).'</loc></url>';
    }

    return response($xml.'</urlset>', 200, ['Content-Type' => 'application/xml']);
})->name('sitemap');
