<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class AcademicHttp
{
    public static function client(): PendingRequest
    {
        return Http::withOptions([
            'verify' => self::verifyOption(),
        ]);
    }

    /**
     * @return bool|string
     */
    public static function verifyOption(): bool|string
    {
        $configured = config('academic.http_verify');

        if ($configured !== null && $configured !== '') {
            if (in_array(strtolower((string) $configured), ['false', '0', 'no', 'off'], true)) {
                return false;
            }

            if (in_array(strtolower((string) $configured), ['true', '1', 'yes', 'on'], true)) {
                return true;
            }

            if (is_string($configured) && file_exists($configured)) {
                return $configured;
            }
        }

        $bundle = config('academic.ca_bundle');

        if (is_string($bundle) && $bundle !== '' && file_exists($bundle)) {
            return $bundle;
        }

        return true;
    }
}
