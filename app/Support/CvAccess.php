<?php

namespace App\Support;

use App\Models\Profile;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;

class CvAccess
{
    public static function profile(): ?Profile
    {
        $profile = Profile::current();

        return $profile?->hasCv() ? $profile : null;
    }

    public static function requiresKey(): bool
    {
        if (! filter_var(SiteSetting::get('cv_require_key', '1'), FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        return filled(SiteSetting::get('cv_download_key'));
    }

    public static function keyIsValid(?string $key): bool
    {
        if (! self::requiresKey()) {
            return true;
        }

        $stored = (string) SiteSetting::get('cv_download_key', '');

        return $key !== null && hash_equals($stored, $key);
    }

    public static function downloadFilename(Profile $profile): string
    {
        $label = $profile->cv_label ?: $profile->name.' CV';
        $extension = pathinfo($profile->cv_path, PATHINFO_EXTENSION) ?: 'pdf';

        $safe = preg_replace('/[^\w\s\-]/', '', $label) ?: 'CV';

        return trim($safe).'.'.$extension;
    }

    public static function downloadResponse(Profile $profile)
    {
        abort_unless(Storage::disk('public')->exists($profile->cv_path), 404);

        return Storage::disk('public')->download(
            $profile->cv_path,
            self::downloadFilename($profile)
        );
    }
}
