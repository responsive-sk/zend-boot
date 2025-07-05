<?php

declare(strict_types=1);

return [
    'database' => [
        // HDM Boot Protocol - Three-Database Foundation
        // Using legacy data/ location for now, will migrate to var/storage/
        'user' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../../data/user.db',
        ],
        'mark' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../../data/mark.db',
        ],
        'system' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../../data/system.db',
        ],

        // HDM Boot Protocol - Future recommended paths
        // TODO: Migrate to these paths using storage() method
        /*
        'user' => [
            'driver' => 'sqlite',
            'database' => 'user.db',  // Will use $pathService->storage('user.db')
        ],
        'mark' => [
            'driver' => 'sqlite',
            'database' => 'mark.db',  // Will use $pathService->storage('mark.db')
        ],
        'system' => [
            'driver' => 'sqlite',
            'database' => 'system.db', // Will use $pathService->storage('system.db')
        ],
        */
    ],
];
