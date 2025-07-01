<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;

// Change to the project root
chdir(dirname(__DIR__));

require 'vendor/autoload.php';

// Build container
$container = require 'config/container.php';

// Create Application, MiddlewareFactory
$app = $container->get(Application::class);
$factory = $container->get(MiddlewareFactory::class);

// Setup pipeline
$app->pipe(\Mezzio\Router\Middleware\RouteMiddleware::class);
$app->pipe(\Mezzio\Router\Middleware\DispatchMiddleware::class);

// Setup routes
$app->get('/', 'App\Handler\HomeHandler', 'home');

// Run the application
$app->run();
