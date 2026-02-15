<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JWT Configuration
    |--------------------------------------------------------------------------
    | Keep secrets in .env. Tokens are signed using HMAC SHA-256.
    | Issuer/Audience are optional but recommended for extra validation.
    */

    'secret' => env('JWT_SECRET', ''),

    'issuer' => env('JWT_ISSUER', 'sme-erp'),

    'audience' => env('JWT_AUDIENCE', 'sme-erp-api'),

    // Access token TTL in minutes
    'access_ttl' => (int) env('JWT_ACCESS_TTL', 60),

    // Allowed clock skew in seconds
    'leeway' => (int) env('JWT_LEEWAY', 60),

    // Timezone for validation
    'timezone' => env('JWT_TIMEZONE', 'UTC'),
];
