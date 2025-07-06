<?php

declare(strict_types=1);

/**
 * User Module Routes
 *
 * All routes related to user authentication and management
 */

return function (\Mezzio\Application $app): void {

    // Authentication routes (require session)
    $app->route('/user/login', [
        \Mezzio\Session\SessionMiddleware::class,
        'User\Handler\LoginHandler'
    ], ['GET', 'POST'], 'user.login');

    $app->get('/user/logout', [
        \Mezzio\Session\SessionMiddleware::class,
        'User\Handler\LogoutHandler'
    ], 'user.logout');

    // Registration (no session needed initially)
    $app->route('/user/register', 'User\Handler\RegistrationHandler', ['GET', 'POST'], 'user.register');

    // Protected routes (require session + authentication)
    $app->get('/user/dashboard', [
        \Mezzio\Session\SessionMiddleware::class,
        \Mezzio\Authentication\AuthenticationMiddleware::class,
        'User\Handler\DashboardHandler'
    ], 'user.dashboard');

    // Admin routes (require session + authentication + admin role)
    $app->get('/user/admin', [
        \Mezzio\Session\SessionMiddleware::class,
        \Mezzio\Authentication\AuthenticationMiddleware::class,
        'User\Middleware\RequireRoleMiddleware',
        'User\Handler\AdminHandler'
    ], 'user.admin');
};
