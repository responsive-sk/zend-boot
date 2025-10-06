<?php

declare(strict_types=1);

return [
    'cache' => [
        'default_ttl' => 3600,
        'prefix' => 'hdm_',
        'driver' => 'file',

        'drivers' => [
            'file' => [
                'path' => 'var/cache',
                'extension' => '.cache',
                'directory_level' => 1,
            ],
            'apc' => [
                'prefix' => 'hdm_',
            ],
        ],

        'namespaces' => [
            'config' => 86400,
            'templates' => 3600,
            'routes' => 7200,
            'database' => 1800,
            'orbit_content' => 3600,
        ],

        'enabled' => [
            'config' => true,
            'templates' => true,
            'routes' => \App\Boot\EnvironmentHandler::isProduction(),
            'database' => true,
        ],
    ],
];