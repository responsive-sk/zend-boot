<?php

declare(strict_types=1);

namespace App\Boot;

/**
 * HDM Boot Protocol - Environment Handler
 *
 * Basic environment detection
 */
class EnvironmentHandler
{
    /**
     * Setup environment for web server
     */
    public static function setupWebEnvironment(): bool
    {
        // Handle built-in PHP server static files
        if (PHP_SAPI === 'cli-server') {
            $scriptFilename = $_SERVER['SCRIPT_FILENAME'] ?? '';
            if (!str_ends_with($scriptFilename, '.php')) {
                return false; // Let PHP server handle static files
            }
        }

        return true;
    }

    /**
     * Get current environment
     */
    public static function getEnvironment(): string
    {
        return $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'production';
    }

    /**
     * Check if in development mode
     */
    public static function isDevelopment(): bool
    {
        return self::getEnvironment() === 'development';
    }

    /**
     * Check if in production mode
     */
    public static function isProduction(): bool
    {
        return self::getEnvironment() === 'production';
    }

    /**
     * Check if in testing mode
     */
    public static function isTesting(): bool
    {
        return self::getEnvironment() === 'testing';
    }

    /**
     * Get cache TTL based on environment
     */
    public static function getCacheTtl(string $cacheType = 'default'): int
    {
        return self::isProduction() ? 3600 : 60;
    }

    /**
     * Check if caching should be enabled
     */
    public static function isCacheEnabled(): bool
    {
        return self::isProduction();
    }
}