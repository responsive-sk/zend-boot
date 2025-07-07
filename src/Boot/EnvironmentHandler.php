<?php

declare(strict_types=1);

namespace App\Boot;

/**
 * HDM Boot Protocol - Environment Handler
 *
 * Handles environment setup and validation
 */
class EnvironmentHandler
{
    /**
     * Setup environment for web server
     * @return bool Returns false for static files on built-in server
     */
    public static function setupWebEnvironment(): bool
    {
        // Handle built-in PHP server static files
        if (self::isBuiltInServer() && !self::isPhpScript()) {
            return false; // Let PHP built-in server handle static files
        }

        // Change to project root
        self::changeToProjectRoot();

        // Set error reporting based on environment
        self::configureErrorReporting();

        return true;
    }

    /**
     * Check if running on built-in PHP server
     */
    private static function isBuiltInServer(): bool
    {
        return PHP_SAPI === 'cli-server';
    }

    /**
     * Check if current request is for a PHP script
     */
    private static function isPhpScript(): bool
    {
        $scriptFilename = $_SERVER['SCRIPT_FILENAME'] ?? '';
        return str_ends_with($scriptFilename, 'index.php') || str_ends_with($scriptFilename, '.php');
    }

    /**
     * Change working directory to project root
     */
    private static function changeToProjectRoot(): void
    {
        $projectRoot = dirname(__DIR__, 2);
        chdir($projectRoot);
    }

    /**
     * Configure error reporting based on environment
     */
    private static function configureErrorReporting(): void
    {
        // Default to production mode for security
        $environment = 'production';

        // Check if development mode is enabled via Laminas development mode
        if (file_exists('config/development.config.php')) {
            $environment = 'development';
        }

        switch ($environment) {
            case 'development':
            case 'dev':
                error_reporting(E_ALL);
                ini_set('display_errors', '1');
                break;

            case 'testing':
            case 'test':
                error_reporting(E_ALL);
                ini_set('display_errors', '0');
                ini_set('log_errors', '1');
                break;

            case 'production':
            case 'prod':
            default:
                error_reporting(E_ERROR | E_WARNING | E_PARSE);
                ini_set('display_errors', '0');
                ini_set('log_errors', '1');
                break;
        }
    }

    /**
     * Get current environment
     */
    public static function getEnvironment(): string
    {
        // Check if development mode is enabled via Laminas development mode
        if (file_exists('config/development.config.php')) {
            return 'development';
        }

        return 'production';
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
        // Testing mode can be detected by PHPUnit environment
        return defined('PHPUNIT_COMPOSER_INSTALL') || getenv('PHPUNIT_RUNNING') === 'true';
    }
}
