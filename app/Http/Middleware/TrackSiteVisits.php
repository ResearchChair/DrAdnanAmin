<?php

namespace App\Http\Middleware;

use App\Services\VisitorAnalyticsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackSiteVisits
{
    public function __construct(protected VisitorAnalyticsService $analytics) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $request->isMethod('GET') || $request->ajax() || $request->prefetch()) {
            return $response;
        }

        if (! $this->analytics->shouldTrack($request->path(), $request->userAgent())) {
            return $response;
        }

        try {
            $visitorKey = $request->cookie(VisitorAnalyticsService::COOKIE);
            $isFreshCookie = false;
            if (! is_string($visitorKey) || ! Str::isUuid($visitorKey)) {
                $visitorKey = (string) Str::uuid();
                $isFreshCookie = true;
            }

            $this->analytics->record(
                $visitorKey,
                '/'.$request->path(),
                $request->route()?->getName(),
                (string) $request->ip(),
                $request->header('CF-IPCountry')
            );

            $this->analytics->forgetSummaryCache();

            if ($isFreshCookie) {
                $response = $response->withCookie(
                    cookie(
                        VisitorAnalyticsService::COOKIE,
                        $visitorKey,
                        60 * 24 * 365 * 2,
                        '/',
                        null,
                        $request->isSecure(),
                        true,
                        false,
                        'Lax'
                    )
                );
            }
        } catch (\Throwable) {
            // Never break the public site for analytics failures
        }

        return $response;
    }
}
