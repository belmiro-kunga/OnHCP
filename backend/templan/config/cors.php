<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Set this via env: FRONTEND_URL (comma-separated for multiple)
    // Example: FRONTEND_URL=http://localhost:5173,http://127.0.0.1:5173
    'allowed_origins' => array_filter(array_map('trim', explode(',', env('FRONTEND_URL', '*')))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Authorization', 'Content-Type', 'X-Requested-With', 'Accept', 'Origin'],

    'exposed_headers' => ['Authorization'],

    // If using cookie-based Sanctum, set to true and don't use wildcard origins
    'supports_credentials' => (bool) env('CORS_SUPPORTS_CREDENTIALS', false),

    'max_age' => 0,
];
