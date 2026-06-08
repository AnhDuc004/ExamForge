<?php

return [
    // Database defaults for ExamForge
    'database' => [
        'connection' => env('DB_CONNECTION', 'pgsql'),
    ],

    'redis' => [
        'connection' => env('REDIS_CONNECTION', 'default'),
    ],

    'queue' => [
        'connection' => env('QUEUE_CONNECTION', 'database'),
    ],

    'tenant' => [
        'subdomain_header' => env('TENANT_SUBDOMAIN_HEADER', null),
    ],
];
