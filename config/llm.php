<?php

return [
    /*
    | Default selection for Application Assistant:
    | auto | openai | groq
    */
    'default' => env('LLM_PROVIDER', 'auto'),

    /*
    | When provider=auto (or primary fails with quota/rate-limit), try in this order.
    */
    'failover_order' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('LLM_FAILOVER_ORDER', 'openai,groq'))
    ))),

    'max_publications' => (int) env('LLM_MAX_PUBLICATIONS', env('OPENAI_MAX_PUBLICATIONS', 15)),
    'rate_limit_per_hour' => (int) env('LLM_RATE_LIMIT_PER_HOUR', env('OPENAI_RATE_LIMIT_PER_HOUR', 20)),
    'cooldown_seconds' => (int) env('LLM_COOLDOWN_SECONDS', 300),

    'providers' => [
        'openai' => [
            'label' => 'OpenAI',
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'timeout' => (int) env('OPENAI_TIMEOUT', 90),
            'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 3500),
        ],
        'groq' => [
            'label' => 'Groq',
            'api_key' => env('GROQ_API_KEY'),
            'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
            'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
            'timeout' => (int) env('GROQ_TIMEOUT', 90),
            'max_tokens' => (int) env('GROQ_MAX_TOKENS', 3500),
        ],
    ],
];
