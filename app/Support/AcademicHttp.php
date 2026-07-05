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

    public static function externalClient(): PendingRequest
    {
        return self::client()
            ->timeout(15)
            ->retry(2, 250)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
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
