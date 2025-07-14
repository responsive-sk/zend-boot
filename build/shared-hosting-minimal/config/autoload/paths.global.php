<?php

declare(strict_types=1);

return [
    'paths' => [
        // Base paths configuration for Mezzio application
        'base_path' => dirname(__DIR__, 2),

        // Core application directories (relative paths)
        'directories' => [
            'config' => 'config',
            'src' => 'src',
            'public' => 'public',
            'data' => 'data',
            'log' => 'log',
            'var' => 'var',
            'vendor' => 'vendor',
            'modules' => 'modules',
            'templates' => 'templates',
        ],

        // Template paths for Twig (relative to base_path)
        'templates' => [
            'app' => 'src/App/templates/app',
            'error' => 'src/App/templates/error',
            'layout' => 'src/App/templates/layout',
            'partial' => 'src/App/templates/partial',
            'page' => 'src/Page/templates/page',
        ],

        // Cache directories
        'cache' => [
            'config' => 'data/cache/config',
            'twig' => 'data/cache/twig',
            'routes' => 'data/cache/routes',
        ],

        // Asset directories
        'assets' => [
            'css' => 'public/css',
            'js' => 'public/js',
            'images' => 'public/images',
            'fonts' => 'public/fonts',
        ],

        // Module-specific paths
        'modules' => [
            'user' => 'modules/User',
            'mark' => 'modules/Mark', // Admin functionality (NEVER admin)
            'blog' => 'modules/Blog',
        ],

        // Custom paths specific to this application
        'custom' => [
            'uploads' => 'public/uploads',
            'downloads' => 'public/downloads',
            'temp' => 'var/tmp',
            'sessions' => 'var/sessions',
            'logs' => 'log',
            'tests' => 'test',
            'docs' => 'docs',
            'bin' => 'bin',
        ],

        // Security configuration for responsive-sk/slim4-paths
        'security' => [
            'enable_path_traversal_protection' => true,
            'enable_encoding_protection' => true,
            'enable_length_validation' => true,
            'max_path_length' => 4096,
            'max_filename_length' => 255,
            'trusted_paths' => [
                // Add trusted paths that bypass some security checks
            ],
        ],

        // Framework preset for responsive-sk/slim4-paths
        'framework_preset' => 'mezzio',
    ],
];
