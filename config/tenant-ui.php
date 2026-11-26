<?php

declare(strict_types=1);

return [
    'cache' => [
        'store' => env('TENANT_UI_CACHE_STORE'),
        'prefix' => 'tenant-ui',
        'ttl_seconds' => (int) env('TENANT_UI_CACHE_TTL', 86400),
        'ttl' => [
            // Mutable indirection (pointer/route): short TTL limits stale reads after cache outages.
            'pointer' => (int) env('TENANT_UI_CACHE_POINTER_TTL', 300),
            'route' => (int) env('TENANT_UI_CACHE_ROUTE_TTL', 300),
            // Immutable version snapshots + merged runtime payloads.
            'version' => (int) env('TENANT_UI_CACHE_VERSION_TTL', 604800),
            'runtime' => (int) env('TENANT_UI_CACHE_RUNTIME_TTL', 86400),
        ],
        'stampede' => [
            'enabled' => (bool) env('TENANT_UI_CACHE_STAMPEDE_LOCK', true),
            'profiles' => ['pointer', 'route', 'runtime'],
            'lock_seconds' => (int) env('TENANT_UI_CACHE_STAMPEDE_LOCK_SECONDS', 10),
            'block_seconds' => (int) env('TENANT_UI_CACHE_STAMPEDE_BLOCK_SECONDS', 5),
        ],
    ],

    'defaults' => [
        'theme' => null,

        'components' => [
            'header' => null,
            'footer' => null,
        ],
    ],

    'runtime_api' => [
        'enabled' => false,
        'prefix' => 'api/ui',
        'middleware' => ['api'],
        'cache_max_age' => (int) env('TENANT_UI_RUNTIME_CACHE_MAX_AGE', 300),
        // Use "public" only when tenant identity is part of the URL/host cache key (not header/session).
        'cache_visibility' => env('TENANT_UI_RUNTIME_CACHE_VISIBILITY', 'private'),
    ],

    'resolvers' => [
        'tenant' => null,
        'modules' => null,
        'locale' => null,
    ],
];
