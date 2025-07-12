<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;

// Load paths configuration for cache path
$paths = require __DIR__ . '/paths.php';
$cacheConfig = ['config_cache_path' => $paths->getPath($paths->base(), $paths->get('legacy_cache') . '/config-cache.php')];

$aggregator = new ConfigAggregator([
    \Mezzio\Router\FastRouteRouter\ConfigProvider::class,
    \Mezzio\Router\ConfigProvider::class,
    \Mezzio\ConfigProvider::class,
\Mezzio\LaminasView\ConfigProvider::class,
Laminas\Form\ConfigProvider::class,
    \Laminas\HttpHandlerRunner\ConfigProvider::class,
    \Laminas\Diactoros\ConfigProvider::class,

    // Mezzio packages
    \Mezzio\Session\ConfigProvider::class,
    \Mezzio\Authentication\ConfigProvider::class,
    \Mezzio\Authentication\Session\ConfigProvider::class,
    \Mezzio\Authorization\ConfigProvider::class,
    \Mezzio\Authorization\Rbac\ConfigProvider::class,

    // User module (after Mezzio packages to override)
    \User\ConfigProvider::class,

    // Mark module (HDM Boot Protocol - mark users only)
    \Mark\ConfigProvider::class,

    // Orbit CMS module
    \Orbit\ConfigProvider::class,

    // Include cache configuration
    new ArrayProvider($cacheConfig),

    // Load dependencies configuration
    new ArrayProvider(require __DIR__ . '/autoload/dependencies.global.php'),

    // Load templates configuration
    new ArrayProvider(require __DIR__ . '/autoload/templates.global.php'),

    // Load session configuration
    new ArrayProvider(require __DIR__ . '/autoload/session.global.php'),

    // Load database configuration
    new ArrayProvider(require __DIR__ . '/autoload/database.global.php'),

    // Load authentication configuration
    new ArrayProvider(require __DIR__ . '/autoload/authentication.global.php'),

    // Load authorization configuration
    new ArrayProvider(require __DIR__ . '/autoload/authorization.global.php'),

    // Load application config
    new ArrayProvider([
        'dependencies' => [
            'factories' => [
                'App\Handler\HomeHandler' => 'App\Handler\HomeHandlerFactory',
                'App\Handler\BootstrapDemoHandler' => 'App\Handler\BootstrapDemoHandlerFactory',
                'App\Handler\MainDemoHandler' => 'App\Handler\MainDemoHandlerFactory',
                'App\Helper\AssetHelper' => 'App\Helper\AssetHelperFactory',
            ],
            'invokables' => [
                'App\Handler\DebugHandler' => 'App\Handler\DebugHandler',
            ],
        ],
    ]),
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
