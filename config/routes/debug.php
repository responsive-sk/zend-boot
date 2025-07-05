<?php

declare(strict_types=1);

/**
 * Debug Routes
 * 
 * Development and debugging routes - should be disabled in production
 */

return function (\Mezzio\Application $app): void {
    
    // Only enable in development
    if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
        
        // Debug route with session for testing
        $app->route('/debug', [
            \Mezzio\Session\SessionMiddleware::class,
            'App\Handler\DebugHandler'
        ], ['GET', 'POST'], 'debug');
        
        // Simple login routes (backup implementation)
        $app->route('/simple-login', 'User\Handler\SimpleLoginHandler', ['GET', 'POST'], 'simple.login');
        $app->get('/simple-dashboard', 'User\Handler\SimpleDashboardHandler', 'simple.dashboard');
        $app->get('/simple-logout', 'User\Handler\SimpleLogoutHandler', 'simple.logout');
    }
};
