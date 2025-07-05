<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;

// Delegate static file requests back to the PHP built-in webserver
if ('cli-server' === PHP_SAPI && __FILE__ !== $_SERVER['SCRIPT_FILENAME']) {
    return false;
}

// Change to the project root
chdir(dirname(__DIR__));

require 'vendor/autoload.php';

// Build container
$container = require 'config/container.php';

// Create Application, MiddlewareFactory
$app = $container->get(Application::class);
$factory = $container->get(MiddlewareFactory::class);

// Setup pipeline
$app->pipe(\Mezzio\Session\SessionMiddleware::class);
$app->pipe(\Mezzio\Router\Middleware\RouteMiddleware::class);
$app->pipe(\Mezzio\Router\Middleware\DispatchMiddleware::class);

// Load routes from separate files
(require __DIR__ . '/../config/routes/app.php')($app);
(require __DIR__ . '/../config/routes/user.php')($app);
(require __DIR__ . '/../config/routes/mark.php')($app, $factory, $container);
(require __DIR__ . '/../config/routes/debug.php')($app);

// Run the application
$app->run();
