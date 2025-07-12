<?php

/**
 * HDM Boot Protocol - Mark Routes
 *
 * Routes accessible only to mark users (mark, editor, supermark roles)
 * Separate from user routes for security and organization
 */

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {

    // Mark authentication middleware - only mark users can access these routes
    $markAuthMiddleware = $container->get(\Mark\Middleware\MarkAuthenticationMiddleware::class);

    // Mark root redirect to dashboard
    $app->get('/mark[/]', [
        $markAuthMiddleware,
        \Mark\Handler\DashboardHandler::class,
    ], 'mark.index');

    // System Dashboard - only for mark users
    $app->get('/mark/dashboard', [
        $markAuthMiddleware,
        \Mark\Handler\DashboardHandler::class,
    ], 'mark.dashboard');

    // System Health - mark users only
    $app->get('/mark/health', [
        $markAuthMiddleware,
        \Mark\Handler\HealthHandler::class,
    ], 'mark.health');

    // System Logs - mark users only
    $app->get('/mark/logs', [
        $markAuthMiddleware,
        \Mark\Handler\LogsHandler::class,
    ], 'mark.logs');

    // Database Management - supermark only
    $app->get('/mark/database', [
        $markAuthMiddleware,
        \Mark\Middleware\SupermarkAuthorizationMiddleware::class,
        \Mark\Handler\DatabaseHandler::class,
    ], 'mark.database');

    // User Management - mark users only
    $app->get('/mark/users', [
        $markAuthMiddleware,
        \Mark\Handler\UserManagementHandler::class,
    ], 'mark.users');

    $app->post('/mark/users/{id:\d+}/edit', [
        $markAuthMiddleware,
        \Mark\Handler\UserEditHandler::class,
    ], 'mark.users.edit');

    $app->post('/mark/users/{id:\d+}/delete', [
        $markAuthMiddleware,
        \Mark\Middleware\SupermarkAuthorizationMiddleware::class,
        \Mark\Handler\UserDeleteHandler::class,
    ], 'mark.users.delete');

    // Mark Management - mark users only
    $app->get('/mark/marks', [
        $markAuthMiddleware,
        \Mark\Handler\MarkManagementHandler::class,
    ], 'mark.marks');

    $app->get('/mark/marks/create', [
        $markAuthMiddleware,
        \Mark\Handler\MarkCreateHandler::class,
    ], 'mark.marks.create');

    $app->post('/mark/marks/create', [
        $markAuthMiddleware,
        \Mark\Handler\MarkCreateHandler::class,
    ]);

    $app->get('/mark/marks/{id:\d+}/edit', [
        $markAuthMiddleware,
        \Mark\Handler\MarkEditHandler::class,
    ], 'mark.marks.edit');

    $app->post('/mark/marks/{id:\d+}/edit', [
        $markAuthMiddleware,
        \Mark\Handler\MarkEditHandler::class,
    ]);

    $app->post('/mark/marks/{id:\d+}/delete', [
        $markAuthMiddleware,
        \Mark\Handler\MarkDeleteHandler::class,
    ], 'mark.marks.delete');

    // System Settings - supermark only
    $app->get('/mark/settings', [
        $markAuthMiddleware,
        \Mark\Middleware\SupermarkAuthorizationMiddleware::class,
        \Mark\Handler\SettingsHandler::class,
    ], 'mark.settings');

    $app->post('/mark/settings', [
        $markAuthMiddleware,
        \Mark\Middleware\SupermarkAuthorizationMiddleware::class,
        \Mark\Handler\SettingsHandler::class,
    ]);

    // Cache Management - mark users only
    $app->get('/mark/cache', [
        $markAuthMiddleware,
        \Mark\Handler\CacheHandler::class,
    ], 'mark.cache');

    $app->post('/mark/cache/clear', [
        $markAuthMiddleware,
        \Mark\Handler\CacheClearHandler::class,
    ], 'mark.cache.clear');

    // Backup Management - supermark only
    $app->get('/mark/backups', [
        $markAuthMiddleware,
        \Mark\Middleware\SupermarkAuthorizationMiddleware::class,
        \Mark\Handler\BackupHandler::class,
    ], 'mark.backups');

    $app->post('/mark/backups/create', [
        $markAuthMiddleware,
        \Mark\Middleware\SupermarkAuthorizationMiddleware::class,
        \Mark\Handler\BackupCreateHandler::class,
    ], 'mark.backups.create');

    // API Routes for mark users
    $app->get('/api/mark/stats', [
        $markAuthMiddleware,
        \Mark\Handler\Api\StatsHandler::class,
    ], 'api.mark.stats');

    $app->get('/api/mark/health', [
        $markAuthMiddleware,
        \Mark\Handler\Api\HealthHandler::class,
    ], 'api.mark.health');

    // Mark Login/Logout (separate from user auth)
    $app->get('/mark/login', [
        \Mark\Handler\LoginHandler::class,
    ], 'mark.login');

    $app->post('/mark/login', [
        \Mark\Handler\LoginHandler::class,
    ]);

    $app->get('/mark/logout', [
        $markAuthMiddleware,
        \Mark\Handler\LogoutHandler::class,
    ], 'mark.logout');

    $app->post('/mark/logout', [
        $markAuthMiddleware,
        \Mark\Handler\LogoutHandler::class,
    ]);
};
