<?php

declare(strict_types=1);

return [
    'ai' => [
        'default' => env('AI_PROVIDER', 'gemini'),
        'timeout' => (int) env('AI_TIMEOUT', 30),
        'providers' => [
            'gemini' => [
                'api_key' => env('GEMINI_API_KEY'),
                'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
            ],
            'groq' => [
                'api_key' => env('GROQ_API_KEY'),
                'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
                'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
            ],
            'openai' => [
                'api_key' => env('OPENAI_API_KEY'),
                'model' => env('OPENAI_MODEL', 'gpt-5-mini'),
                'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            ],
        ],
    ],
    'credits' => [
        'website' => (int) env('CREDITS_WEBSITE', 10),
        'page' => (int) env('CREDITS_PAGE', 2),
        'blog' => (int) env('CREDITS_BLOG', 3),
        'regenerate' => (int) env('CREDITS_REGENERATE', 2),
    ],
    'payments' => [
        'bkash_number' => env('BKASH_NUMBER'),
        'bep20_address' => env('BEP20_USDT_ADDRESS'),
        'bep20_network' => env('BEP20_NETWORK', 'BSC'),
    ],
    'admin' => [
        'emails' => array_values(array_filter(array_map(
            static fn (string $email): string => strtolower(trim($email)),
            explode(',', (string) env('ADMIN_EMAILS', env('ADMIN_EMAIL', '')))
        ))),
    ],
];
