<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RestrictAdminByIp
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = config('admin_security.allowed_ips', []);

        if ($allowedIps === []) {
            return $next($request);
        }

        if (! in_array($request->ip(), $allowedIps, true)) {
            Log::warning('Admin access denied for IP', [
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            abort(Response::HTTP_FORBIDDEN, 'Access denied.');
        }

        return $next($request);
    }
}
