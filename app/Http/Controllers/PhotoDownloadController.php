<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Support\PublicStorage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhotoDownloadController extends Controller
{
    public function __invoke(): StreamedResponse|BinaryFileResponse
    {
        $profile = Profile::current();

        abort_unless(filled($profile->photo_path), 404);

        $path = ltrim($profile->photo_path, '/');
        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';
        $filename = Str::slug($profile->name).'-photo.'.$extension;
        $disk = PublicStorage::disk();

        if ($disk->exists($path)) {
            return $disk->download($path, $filename);
        }

        $absolute = storage_path('app/public/'.$path);

        abort_unless(is_file($absolute), 404);

        return response()->download($absolute, $filename);
    }
}
