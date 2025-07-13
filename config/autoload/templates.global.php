<?php

declare(strict_types=1);

// Load paths configuration
$paths = require dirname(__DIR__) . '/paths.php';

return [
    'templates' => [
        'extension' => 'phtml',
        'paths' => [
            // User module templates
            'user' => [$paths->get('user_templates') . '/user'],

            // Mark module templates (HDM Boot Protocol)
            'mark' => [$paths->get('mark_templates') . '/mark'],

            // Orbit module templates
            'orbit' => [$paths->get('orbit_templates') . '/orbit'],

            // App templates
            'app' => [$paths->get('app_templates')],

            // Layout templates
            'layout' => [$paths->get('layouts')],

            // Error templates
            'error' => [$paths->get('error_templates')],

            // Default templates path (must be last)
            'default' => [$paths->get('templates')],
        ],
    ],
];
