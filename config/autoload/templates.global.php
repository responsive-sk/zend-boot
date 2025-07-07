<?php

declare(strict_types=1);

return [
    'templates' => [
        'extension' => 'phtml',
        'paths' => [
            // User module templates
            'user' => [__DIR__ . '/../../modules/User/templates/user'],

            // Mark module templates (HDM Boot Protocol)
            'mark' => [__DIR__ . '/../../modules/Mark/templates/mark'],

            // App templates
            'app' => [__DIR__ . '/../../templates/app'],

            // Layout templates
            'layout' => [__DIR__ . '/../../templates/layout'],

            // Default templates path (must be last)
            'default' => [__DIR__ . '/../../templates'],
        ],
    ],
];
