<?php

declare(strict_types=1);

use League\Flysystem\Local\LocalFilesystemAdapter;

$rootDir = dirname(__DIR__, 2);

return [
    'paths' => [
        'root' => $rootDir,
        'public' => "$rootDir/public",
        'templates' => "$rootDir/templates",
        'themes' => "$rootDir/themes",
        'uploads' => "$rootDir/data/uploads",
        'config' => dirname(__DIR__),
        'cache' => "$rootDir/data/cache",
    ],
    'flysystem' => [
        'adapters' => [
            'local.public' => [
                'class' => LocalFilesystemAdapter::class,
                'args' => ["$rootDir/public"]
            ],
            'local.themes' => [
                'class' => LocalFilesystemAdapter::class,
                'args' => ["$rootDir/themes"]
            ],
            'local.uploads' => [
                'class' => LocalFilesystemAdapter::class,
                'args' => ["$rootDir/data/uploads"]
            ]
        ]
    ]
];
