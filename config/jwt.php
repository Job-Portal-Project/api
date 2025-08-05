<?php

return [
    'public_key_path' => env('JWT_PUBLIC_KEY', storage_path('jwt/public_key.pem')),
    'private_key_path' => env('JWT_PRIVATE_KEY', storage_path('jwt/private_key.pem')),
    'access' => [
        'ttl' => 30, // Time to live for access tokens (in minutes)
        'cbu' => 0,  // Can be used after (in minutes)
    ],
    'refresh' => [
        'ttl' => 21600, // Time to live for refresh tokens (in minutes - 15 days)
        'cbu' => 0,       // Can be used after (in minutes)
    ],
];
