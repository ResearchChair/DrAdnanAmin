<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleUnauthenticatedAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            return $next($request);
        }

        $limit = config('admin_security.panel_request_limit', 40);
        $key = 'admin-panel-access:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            abort(Response::HTTP_TOO_MANY_REQUESTS, 'Too many requests. Please try again later.');
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
