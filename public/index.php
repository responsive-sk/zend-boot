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

// Setup routes
$app->get('/', 'App\Handler\HomeHandler', 'home');
$app->get('/bootstrap-demo', 'App\Handler\BootstrapDemoHandler', 'bootstrap-demo');
$app->get('/main-demo', 'App\Handler\MainDemoHandler', 'main-demo');

// User module routes
$app->route('/user/login', ['User\Handler\LoginHandler', 'User\Middleware\CsrfMiddleware'], ['GET', 'POST'], 'user.login');
$app->route('/user/register', ['User\Handler\RegistrationHandler', 'User\Middleware\CsrfMiddleware'], ['GET', 'POST'], 'user.register');
$app->get('/user/logout', 'User\Handler\LogoutHandler', 'user.logout');

// Protected routes
$app->get('/user/dashboard', [
    'User\Middleware\RequireLoginMiddleware',
    'User\Handler\DashboardHandler'
], 'user.dashboard');

$app->get('/user/admin', [
    'User\Middleware\RequireLoginMiddleware',
    'User\Middleware\RequireRoleMiddleware',
    'User\Handler\AdminHandler'
], 'user.admin');

// Run the application
$app->run();
