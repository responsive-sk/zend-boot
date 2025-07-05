<?php

declare(strict_types=1);

namespace App\Boot;

use Mezzio\Application;
use Psr\Container\ContainerInterface;

/**
 * HDM Boot Protocol - Application Factory
 *
 * Main factory for creating fully configured application
 * This is the single entry point for application creation
 */
class ApplicationFactory
{
    /**
     * Create fully configured application
     */
    public static function create(): Application
    {
        // Setup environment - exit early for static files
        if (!EnvironmentHandler::setupWebEnvironment()) {
            exit(0);
        }

        // Build container
        $container = ContainerBuilder::buildDefault();

        // Bootstrap application
        $bootstrap = new ApplicationBootstrap($container);

        return $bootstrap->bootstrap();
    }

    /**
     * Create application with custom container
     */
    public static function createWithContainer(ContainerInterface $container): Application
    {
        // Setup environment - exit early for static files
        if (!EnvironmentHandler::setupWebEnvironment()) {
            exit(0);
        }

        // Bootstrap application
        $bootstrap = new ApplicationBootstrap($container);

        return $bootstrap->bootstrap();
    }

    /**
     * Create application for testing
     */
    public static function createForTesting(): Application
    {
        // Don't setup web environment for testing
        $container = ContainerBuilder::buildDefault();
        $bootstrap = new ApplicationBootstrap($container);

        return $bootstrap->bootstrap();
    }
}
