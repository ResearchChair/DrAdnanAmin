<?php

return [
    'api_key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    'timeout' => (int) env('OPENAI_TIMEOUT', 90),
    'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 3500),
    'max_publications' => (int) env('OPENAI_MAX_PUBLICATIONS', 15),
    'rate_limit_per_hour' => (int) env('OPENAI_RATE_LIMIT_PER_HOUR', 20),
];
