<?php

declare(strict_types=1);

namespace App\Boot;

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;
use ResponsiveSk\Slim4Paths\Paths;
use Psr\SimpleCache\CacheInterface;

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
    private Paths $paths;
    private ?CacheInterface $cache = null;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $app = $container->get(Application::class);
        assert($app instanceof Application);
        $this->app = $app;

        $factory = $container->get(MiddlewareFactory::class);
        assert($factory instanceof MiddlewareFactory);
        $this->factory = $factory;

        $paths = $container->get(Paths::class);
        assert($paths instanceof Paths);
        $this->paths = $paths;

        // Initialize cache if available
        if ($container->has(CacheInterface::class)) {
            $this->cache = $container->get(CacheInterface::class);
        }
    }

    /**
     * Bootstrap the complete application
     */
    public function bootstrap(): Application
    {
        $this->setupCaching();
        $this->setupMiddlewarePipeline();
        $this->loadRoutes();

        return $this->app;
    }

    /**
     * Setup caching configuration
     */
    private function setupCaching(): void
    {
        if (!$this->cache) {
            return;
        }

        // Cache configuration based on environment
        if (EnvironmentHandler::isProduction()) {
            $this->enableProductionCaching();
        } elseif (EnvironmentHandler::isDevelopment()) {
            $this->enableDevelopmentCaching();
        }

        // Cache warmup for production
        if (EnvironmentHandler::isProduction()) {
            $this->warmupCache();
        }
    }

    /**
     * Enable aggressive caching for production
     */
    private function enableProductionCaching(): void
    {
        // Cache route configuration
        if ($this->cache->has('routes_loaded')) {
            $this->loadCachedRoutes();
        }

        // Cache template configuration
        $this->cacheTemplates();
    }

    /**
     * Enable development caching (shorter TTL)
     */
    private function enableDevelopmentCaching(): void
    {
        // Development caching with shorter TTL
        // Config is cached for 1 minute, templates for 5 minutes
    }

    /**
     * Warm up cache for production
     */
    private function warmupCache(): void
    {
        $cacheKey = 'app_warmup_' . md5(__FILE__ . filemtime(__FILE__));

        if (!$this->cache->has($cacheKey)) {
            // Pre-cache common configurations
            $this->precacheConfigurations();
            $this->cache->set($cacheKey, true, 3600); // 1 hour
        }
    }

    /**
     * Pre-cache common configurations
     */
    private function precacheConfigurations(): void
    {
        // Pre-cache template paths
        $templateConfig = $this->container->get('config')['templates'] ?? [];
        $this->cache->set('template_paths', $templateConfig, 3600);

        // Pre-cache route patterns
        $routePatterns = $this->extractRoutePatterns();
        $this->cache->set('route_patterns', $routePatterns, 7200);
    }

    /**
     * Extract route patterns for caching
     */
    private function extractRoutePatterns(): array
    {
        $patterns = [];
        $routeConfigs = [
            'app' => $this->paths->getPath($this->paths->get('routes'), 'app.php'),
            'user' => $this->paths->getPath($this->paths->get('routes'), 'user.php'),
            'mark' => $this->paths->getPath($this->paths->get('routes'), 'mark.php'),
            'orbit' => $this->paths->getPath($this->paths->get('routes'), 'orbit.php'),
        ];

        foreach ($routeConfigs as $name => $configPath) {
            if (file_exists($configPath)) {
                $patterns[$name] = $this->analyzeRouteFile($configPath);
            }
        }

        return $patterns;
    }

    /**
     * Analyze route file for patterns
     */
    private function analyzeRouteFile(string $filepath): array
    {
        $content = file_get_contents($filepath);
        $patterns = [];

        // Extract route patterns (simplified) - OPRAVA: preg_match_all namiesto preg_all
        if (preg_match_all('/->(get|post|put|delete|patch|route)\(\s*[\'"]([^\'"]+)[\'"]/i', $content, $matches)) {
            $patterns = $matches[2];
        }

        return $patterns;
    }

    /**
     * Load routes from cache (production optimization)
     */
    private function loadCachedRoutes(): void
    {
        // This would load pre-compiled route configuration
        // For now, we'll use the standard loading but cache the result
        $this->loadRoutes();

        // Cache the route configuration for next request
        if ($this->cache) {
            $this->cache->set('routes_loaded', true, EnvironmentHandler::getCacheTtl('routes'));
        }
    }

    /**
     * Cache template configurations
     */
    private function cacheTemplates(): void
    {
        if (!$this->cache) {
            return;
        }

        $templateConfig = $this->container->get('config')['templates'] ?? [];
        $this->cache->set('template_config', $templateConfig, EnvironmentHandler::getCacheTtl('templates'));
    }

    /**
     * Setup middleware pipeline in correct order
     */
    private function setupMiddlewarePipeline(): void
    {
        // Session middleware - must be first for authentication
        $this->app->pipe(\Mezzio\Session\SessionMiddleware::class);

        // Cache middleware for production - DOČASNE VYPNUTÉ kým neexistuje
        // if (EnvironmentHandler::isProduction() && $this->cache) {
        //     $this->app->pipe(\App\Middleware\CacheMiddleware::class);
        // }

        // Router middleware
        $this->app->pipe(\Mezzio\Router\Middleware\RouteMiddleware::class);

        // Dispatch middleware - must be last
        $this->app->pipe(\Mezzio\Router\Middleware\DispatchMiddleware::class);
    }

    /**
     * Load all route configurations using Paths service
     */
    private function loadRoutes(): void
    {
        $routeConfigs = [
            'app' => $this->paths->getPath($this->paths->get('routes'), 'app.php'),
            'user' => $this->paths->getPath($this->paths->get('routes'), 'user.php'),
            'mark' => $this->paths->getPath($this->paths->get('routes'), 'mark.php'),
            'orbit' => $this->paths->getPath($this->paths->get('routes'), 'orbit.php'),
            'debug' => $this->paths->getPath($this->paths->get('routes'), 'debug.php'),
        ];

        foreach ($routeConfigs as $name => $configPath) {
            if (file_exists($configPath)) {
                $this->loadRouteConfig($name, $configPath);
            }
        }

        // Cache route loading completion
        if ($this->cache) {
            $this->cache->set('routes_loaded', true, EnvironmentHandler::getCacheTtl('routes'));
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

            case 'orbit':
                // Orbit routes need app and container
                $routeLoader($this->app, $this->container);
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

    /**
     * Get the cache service
     */
    public function getCache(): ?CacheInterface
    {
        return $this->cache;
    }
}