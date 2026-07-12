<?php

namespace App\Services;

use App\Models\SitePageView;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class VisitorAnalyticsService
{
    public const COOKIE = 'site_vid';

    /**
     * @return array{country_code: ?string, country_name: ?string}
     */
    public function resolveCountry(?string $ip, ?string $cfCountry = null): array
    {
        $cf = strtoupper(trim((string) $cfCountry));
        if ($cf !== '' && $cf !== 'XX' && strlen($cf) <= 3) {
            return [
                'country_code' => $cf,
                'country_name' => $this->countryNameFromCode($cf),
            ];
        }

        if (! filled($ip) || $this->isPrivateIp($ip)) {
            return ['country_code' => null, 'country_name' => 'Local / Unknown'];
        }

        $cacheKey = 'geo:ip:'.hash('sha256', $ip);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($ip) {
            try {
                $response = Http::timeout(2)
                    ->get("http://ip-api.com/json/{$ip}", [
                        'fields' => 'status,country,countryCode',
                    ]);

                if ($response->successful() && $response->json('status') === 'success') {
                    return [
                        'country_code' => strtoupper((string) $response->json('countryCode')),
                        'country_name' => (string) $response->json('country'),
                    ];
                }
            } catch (\Throwable) {
                // ignore geo failures
            }

            return ['country_code' => null, 'country_name' => 'Unknown'];
        });
    }

    public function record(string $visitorKey, string $path, ?string $routeName, string $ip, ?string $cfCountry): void
    {
        $path = '/'.ltrim(Str::limit($path, 180, ''), '/');
        if ($path === '//') {
            $path = '/';
        }

        $isNew = ! SitePageView::query()->where('visitor_key', $visitorKey)->exists();
        $geo = $this->resolveCountry($ip, $cfCountry);

        SitePageView::query()->create([
            'visitor_key' => $visitorKey,
            'path' => $path,
            'page_label' => $this->labelFor($path, $routeName),
            'country_code' => $geo['country_code'],
            'country_name' => $geo['country_name'],
            'is_new_visitor' => $isNew,
            'ip_hash' => hash('sha256', $ip.'|'.(string) config('app.key')),
        ]);
    }

    /**
     * @return array{
     *   total_views: int,
     *   unique_visitors: int,
     *   new_visitors: int,
     *   returning_visitors: int,
     *   by_page: list<array{label: string, path: string, views: int, unique: int}>,
     *   by_country: list<array{country: string, code: ?string, views: int, unique: int}>
     * }
     */
    public function summary(): array
    {
        return Cache::remember('visitor-analytics:summary:v1', now()->addMinutes(5), function () {
            $totalViews = (int) SitePageView::query()->count();
            $uniqueVisitors = (int) SitePageView::query()->distinct('visitor_key')->count('visitor_key');
            $newVisitors = (int) SitePageView::query()->where('is_new_visitor', true)->count();
            $returningVisitors = max(0, $uniqueVisitors - $newVisitors);

            // Visitors with more than one page view = repeaters (engagement)
            $repeaters = (int) SitePageView::query()
                ->select('visitor_key')
                ->groupBy('visitor_key')
                ->havingRaw('COUNT(*) > 1')
                ->get()
                ->count();

            $byPage = SitePageView::query()
                ->select([
                    'page_label',
                    'path',
                    DB::raw('COUNT(*) as views'),
                    DB::raw('COUNT(DISTINCT visitor_key) as unique_visitors'),
                ])
                ->groupBy('page_label', 'path')
                ->orderByDesc('views')
                ->limit(20)
                ->get()
                ->map(fn ($row) => [
                    'label' => $row->page_label ?: $row->path,
                    'path' => $row->path,
                    'views' => (int) $row->views,
                    'unique' => (int) $row->unique_visitors,
                ])
                ->all();

            $byCountry = SitePageView::query()
                ->select([
                    'country_name',
                    'country_code',
                    DB::raw('COUNT(*) as views'),
                    DB::raw('COUNT(DISTINCT visitor_key) as unique_visitors'),
                ])
                ->groupBy('country_name', 'country_code')
                ->orderByDesc('views')
                ->limit(25)
                ->get()
                ->map(fn ($row) => [
                    'country' => $row->country_name ?: 'Unknown',
                    'code' => $row->country_code,
                    'views' => (int) $row->views,
                    'unique' => (int) $row->unique_visitors,
                ])
                ->all();

            return [
                'total_views' => $totalViews,
                'unique_visitors' => $uniqueVisitors,
                'new_visitors' => $newVisitors,
                'returning_visitors' => $returningVisitors,
                'repeat_visitors' => $repeaters,
                'by_page' => $byPage,
                'by_country' => $byCountry,
            ];
        });
    }

    public function forgetSummaryCache(): void
    {
        Cache::forget('visitor-analytics:summary:v1');
    }

    public function labelFor(string $path, ?string $routeName): string
    {
        $map = [
            'home' => 'Home',
            'about' => 'Biography',
            'publications' => 'Publications',
            'research' => 'Research',
            'students' => 'Scholars',
            'training' => 'Training',
            'services' => 'Services',
            'gallery' => 'Gallery',
            'contact' => 'Contact',
            'cv.show' => 'CV',
            'photo.download' => 'Photo download',
        ];

        if ($routeName && isset($map[$routeName])) {
            return $map[$routeName];
        }

        $normalized = rtrim($path, '/') ?: '/';

        return match (true) {
            $normalized === '/' => 'Home',
            str_starts_with($normalized, '/about') => 'Biography',
            str_starts_with($normalized, '/publications') => 'Publications',
            str_starts_with($normalized, '/research') => 'Research',
            str_starts_with($normalized, '/students') => 'Scholars',
            str_starts_with($normalized, '/training') => 'Training',
            str_starts_with($normalized, '/services') => 'Services',
            str_starts_with($normalized, '/gallery') => 'Gallery',
            str_starts_with($normalized, '/contact') => 'Contact',
            default => Str::limit($normalized, 40),
        };
    }

    public function shouldTrack(?string $path, ?string $userAgent): bool
    {
        $path = '/'.ltrim((string) $path, '/');
        if ($path === '//') {
            $path = '/';
        }

        $skipPrefixes = [
            '/admin',
            '/livewire',
            '/storage',
            '/up',
            '/build',
            '/vendor',
            '/_debugbar',
            '/favicon',
            '/sitemap',
        ];

        foreach ($skipPrefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return false;
            }
        }

        if (preg_match('/\.(css|js|map|jpg|jpeg|png|gif|webp|svg|ico|woff2?|ttf|eot)$/i', $path)) {
            return false;
        }

        $ua = Str::lower((string) $userAgent);
        if ($ua !== '' && preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|wget|curl|python-requests|scrapy/i', $ua)) {
            return false;
        }

        return true;
    }

    protected function isPrivateIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }

    protected function countryNameFromCode(string $code): string
    {
        $names = [
            'PK' => 'Pakistan',
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'AE' => 'United Arab Emirates',
            'SA' => 'Saudi Arabia',
            'IN' => 'India',
            'DE' => 'Germany',
            'FI' => 'Finland',
            'PT' => 'Portugal',
            'CN' => 'China',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'FR' => 'France',
            'NL' => 'Netherlands',
            'NO' => 'Norway',
            'SE' => 'Sweden',
            'TR' => 'Turkey',
            'MY' => 'Malaysia',
            'SG' => 'Singapore',
            'KZ' => 'Kazakhstan',
        ];

        return $names[$code] ?? $code;
    }
}
