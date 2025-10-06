<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

// Load paths configuration
$paths = require __DIR__ . '/paths.php';

// Basic cache configuration
$cacheConfig = [
    'config_cache_path' => $paths->get('cache') . '/config-cache.php',
];

$aggregator = new ConfigAggregator([
    // Mezzio framework providers
    \Mezzio\Router\FastRouteRouter\ConfigProvider::class,
    \Mezzio\Router\ConfigProvider::class,
    \Mezzio\ConfigProvider::class,
    \Mezzio\LaminasView\ConfigProvider::class,

    // Laminas components
    \Laminas\Form\ConfigProvider::class,
    \Laminas\HttpHandlerRunner\ConfigProvider::class,
    \Laminas\Diactoros\ConfigProvider::class,

    // Mezzio packages
    \Mezzio\Session\ConfigProvider::class,
    \Mezzio\Authentication\ConfigProvider::class,
    \Mezzio\Authentication\Session\ConfigProvider::class,
    \Mezzio\Authorization\ConfigProvider::class,
    \Mezzio\Authorization\Rbac\ConfigProvider::class,

    // Application modules
    \User\ConfigProvider::class,
    \Mark\ConfigProvider::class,
    \Orbit\ConfigProvider::class,

    // Include cache configuration
    new ArrayProvider($cacheConfig),

    // Load application configurations
    new ArrayProvider(require __DIR__ . '/autoload/dependencies.global.php'),
    new ArrayProvider(require __DIR__ . '/autoload/templates.global.php'),
    new ArrayProvider(require __DIR__ . '/autoload/session.global.php'),
    new ArrayProvider(require __DIR__ . '/autoload/database.global.php'),
    new ArrayProvider(require __DIR__ . '/autoload/authentication.global.php'),
    new ArrayProvider(require __DIR__ . '/autoload/authorization.global.php'),

    // Load application-specific config
    new ArrayProvider([
        'dependencies' => [
            'invokables' => [
                \App\Handler\DebugHandler::class => \App\Handler\DebugHandler::class,
            ],
            'factories' => [
                // Core application handlers
                \App\Handler\HomeHandler::class => \App\Handler\HomeHandlerFactory::class,
                \App\Handler\BootstrapDemoHandler::class => \App\Handler\BootstrapDemoHandlerFactory::class,
                \App\Handler\MainDemoHandler::class => \App\Handler\MainDemoHandlerFactory::class,

                // Helpers
                \App\Helper\AssetHelper::class => \App\Helper\AssetHelperFactory::class,

                // Template renderer
                \Mezzio\Template\TemplateRendererInterface::class => \Mezzio\LaminasView\LaminasViewRendererFactory::class,
            ],
        ],
    ]),

], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();