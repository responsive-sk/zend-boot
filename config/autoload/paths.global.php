<?php

declare(strict_types=1);

use League\Flysystem\Local\LocalFilesystemAdapter;

$rootDir = dirname(__DIR__, 2);

return [
    'paths' => [
        // HDM Boot Protocol - Core paths
        'root' => $rootDir,
        'public' => "$rootDir/public",
        'config' => dirname(__DIR__),

        // HDM Boot Protocol - Organized directory structure (PILLAR VI)
        'storage' => "$rootDir/var/storage",    // Database files (RECOMMENDED)
        'logs' => "$rootDir/var/logs",          // Application logs
        'cache' => "$rootDir/var/cache",        // Cache files
        'sessions' => "$rootDir/var/sessions",  // Session data

        // Content and assets
        'content' => "$rootDir/content",        // Content files (Git-friendly)
        'templates' => "$rootDir/templates",    // Template files
        'themes' => "$rootDir/themes",          // Theme files
        'uploads' => "$rootDir/public/uploads", // User uploads (web accessible)

        // Legacy compatibility (will be migrated)
        'data' => "$rootDir/data",              // Current database location
        'legacy_cache' => "$rootDir/data/cache", // Legacy cache location
        'legacy_uploads' => "$rootDir/data/uploads", // Legacy uploads location
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
