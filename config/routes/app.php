<?php

declare(strict_types=1);

/**
 * Application Routes
 *
 * Main application routes that don't require authentication
 */

return function (\Mezzio\Application $app): void {

    // Public routes (no session needed)
    $app->get('/', 'App\Handler\HomeHandler', 'home');
    $app->get('/bootstrap-demo', 'App\Handler\BootstrapDemoHandler', 'bootstrap-demo');
    $app->get('/main-demo', 'App\Handler\MainDemoHandler', 'main-demo');
};
