<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;

$cacheConfig = ['config_cache_path' => 'data/cache/config-cache.php'];

$aggregator = new ConfigAggregator([
    \Mezzio\Router\FastRouteRouter\ConfigProvider::class,
    \Mezzio\Router\ConfigProvider::class,
    \Mezzio\ConfigProvider::class,
    \Laminas\HttpHandlerRunner\ConfigProvider::class,
    \Laminas\Diactoros\ConfigProvider::class,

    // Include cache configuration
    new ArrayProvider($cacheConfig),

    // Load application config
    new ArrayProvider([
        'dependencies' => [
            'factories' => [
                'App\Handler\HomeHandler' => 'App\Handler\HomeHandlerFactory',
                'App\Handler\BootstrapDemoHandler' => 'App\Handler\BootstrapDemoHandlerFactory',
                'App\Handler\MainDemoHandler' => 'App\Handler\MainDemoHandlerFactory',
                'App\Helper\AssetHelper' => 'App\Helper\AssetHelperFactory',
            ],
        ],
    ]),
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
