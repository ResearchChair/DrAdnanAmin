<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin login brute-force protection
    |--------------------------------------------------------------------------
    */

    'login_max_attempts' => (int) env('ADMIN_LOGIN_MAX_ATTEMPTS', 5),

    'login_decay_seconds' => (int) env('ADMIN_LOGIN_DECAY_SECONDS', 300),

    /*
    |--------------------------------------------------------------------------
    | Unauthenticated admin route throttling (requests per minute, per IP)
    |--------------------------------------------------------------------------
    */

    'panel_request_limit' => (int) env('ADMIN_PANEL_REQUEST_LIMIT', 40),

    /*
    |--------------------------------------------------------------------------
    | Optional IP allowlist (comma-separated). Leave empty to allow all IPs.
    | Example: ADMIN_ALLOWED_IPS=127.0.0.1,::1,203.0.113.10
    |--------------------------------------------------------------------------
    */

    'allowed_ips' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ADMIN_ALLOWED_IPS', ''))
    ))),

    'username' => env('ADMIN_USERNAME', 'admin'),

    'login_email' => env('ADMIN_LOGIN_EMAIL', 'admin@portfolio.local'),

    'password' => env('ADMIN_PASSWORD'),

];
