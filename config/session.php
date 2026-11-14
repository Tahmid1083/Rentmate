<?php

return [

    'default' => env('SESSION_DRIVER', 'database'),

    'connections' => [

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/sessions'),
            'lifetime' => env('SESSION_LIFETIME', 120),
            'lottery' => [2, 100],
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('SESSION_CONNECTION', null),
            'table' => 'sessions',
            'lifetime' => env('SESSION_LIFETIME', 120),
            'lottery' => [2, 100],
            'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),
        ],

        'cookie' => [
            'driver' => 'cookie',
            'lifetime' => env('SESSION_LIFETIME', 120),
            'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),
            'lottery' => [2, 100],
        ],

    ],

    'lifetime' => env('SESSION_LIFETIME', 120),
    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),
    'encrypt' => env('SESSION_ENCRYPT', false),
    'cookie' => env('SESSION_COOKIE', 'rental_management_session'),
    'path' => env('SESSION_PATH', '/'),
    'domain' => env('SESSION_DOMAIN'),
    'secure' => env('SESSION_SECURE_COOKIE'),
    'http_only' => env('SESSION_HTTP_ONLY', true),
    'same_site' => env('SESSION_SAME_SITE', 'lax'),

];
