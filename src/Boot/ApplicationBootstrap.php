<?php

declare(strict_types=1);

namespace App\Boot;

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

/**
 * HDM Boot Protocol - Application Bootstrap
 *
 * Centralized application initialization and configuration
 * Keeps index.php clean and moves all logic here
 */
class ApplicationBootstrap
{
    private ContainerInterface $container;
    private Application $app;
    private MiddlewareFactory $factory;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $app = $container->get(Application::class);
        assert($app instanceof Application);
        $this->app = $app;

        $factory = $container->get(MiddlewareFactory::class);
        assert($factory instanceof MiddlewareFactory);
        $this->factory = $factory;
    }

    /**
     * Bootstrap the complete application
     */
    public function bootstrap(): Application
    {
        $this->setupMiddlewarePipeline();
        $this->loadRoutes();

        return $this->app;
    }

    /**
     * Setup middleware pipeline in correct order
     */
    private function setupMiddlewarePipeline(): void
    {
        // Session middleware - must be first for authentication
        $this->app->pipe(\Mezzio\Session\SessionMiddleware::class);

        // Router middleware
        $this->app->pipe(\Mezzio\Router\Middleware\RouteMiddleware::class);

        // Dispatch middleware - must be last
        $this->app->pipe(\Mezzio\Router\Middleware\DispatchMiddleware::class);
    }

    /**
     * Load all route configurations
     */
    private function loadRoutes(): void
    {
        $routeConfigs = [
            'app' => __DIR__ . '/../../config/routes/app.php',
            'user' => __DIR__ . '/../../config/routes/user.php',
            'mark' => __DIR__ . '/../../config/routes/mark.php',
            'debug' => __DIR__ . '/../../config/routes/debug.php',
        ];

        foreach ($routeConfigs as $name => $configPath) {
            if (file_exists($configPath)) {
                $this->loadRouteConfig($name, $configPath);
            }
        }
    }

    /**
     * Load individual route configuration
     */
    private function loadRouteConfig(string $name, string $configPath): void
    {
        $routeLoader = require $configPath;

        // Different route configs have different signatures
        switch ($name) {
            case 'mark':
                // Mark routes need factory and container
                $routeLoader($this->app, $this->factory, $this->container);
                break;

            case 'app':
            case 'user':
            case 'debug':
            default:
                // Standard routes just need app
                $routeLoader($this->app);
                break;
        }
    }

    /**
     * Get the configured application
     */
    public function getApplication(): Application
    {
        return $this->app;
    }

    /**
     * Get the container
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Get the middleware factory
     */
    public function getMiddlewareFactory(): MiddlewareFactory
    {
        return $this->factory;
    }
}
