<?php

declare(strict_types=1);

return [
    'templates' => [
        'paths' => [
            // Default templates path
            '' => [__DIR__ . '/../../templates'],
            
            // User module templates
            'user' => [__DIR__ . '/../../modules/User/templates/user'],
            
            // App templates
            'app' => [__DIR__ . '/../../templates/app'],
        ],
    ],
];
