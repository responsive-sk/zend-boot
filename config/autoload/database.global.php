<?php

declare(strict_types=1);

return [
    'database' => [
        'user' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../../data/user.db',
        ],
        'mark' => [
            'driver' => 'sqlite', 
            'database' => __DIR__ . '/../../data/mark.db',
        ],
    ],
];
