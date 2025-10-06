<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ResponsiveSk\Slim4Paths\Paths;
use Psr\SimpleCache\CacheInterface;
use App\Service\CacheService;

return [
    'dependencies' => [
        'factories' => [
            // Modern Paths Service with MezzioOrbit preset
            Paths::class => \App\Service\Factory\PathsServiceFactory::class,

            // HDM Boot Protocol - Unified Path Service (PILLAR III)
            \App\Service\PathServiceInterface::class => \App\Service\UnifiedPathServiceFactory::class,

            // Cache services
            CacheInterface::class => \App\Service\Factory\CacheServiceFactory::class,
            CacheService::class => \App\Service\Factory\CacheServiceFactory::class,

            // Template renderer with cache support
            \Mezzio\Template\TemplateRendererInterface::class => function (ContainerInterface $container) {
                $config = $container->get('config');
                $cacheConfig = $config['cache'] ?? [];

                // Use enhanced PhpRenderer with cache support
                $renderer = $container->get(\App\Template\PhpRenderer::class);

                // Enable template cache based on configuration
                if (($cacheConfig['enabled']['templates'] ?? true) && \App\Boot\EnvironmentHandler::isProduction()) {
                    $cache = $container->get(CacheInterface::class);
                    $templateCache = $cache->withNamespace('templates');
                    $renderer->setCache($templateCache, $cacheConfig['namespaces']['templates'] ?? 3600);
                    $renderer->enableCache(true);
                }

                return $renderer;
            },

            // Database services with cache wrapper
            'pdo.user' => \App\Database\PdoFactory::class,
            'pdo.mark' => \App\Database\PdoFactory::class,
            'pdo.system' => \App\Database\PdoFactory::class,
            \App\Database\MigrationService::class => \App\Database\MigrationServiceFactory::class,

        ],

        'aliases' => [
            'cache' => CacheInterface::class,
        ],
    ],
];