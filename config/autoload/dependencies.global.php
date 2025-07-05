<?php

declare(strict_types=1);

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Psr\Container\ContainerInterface;

return [
    'dependencies' => [
        'factories' => [
            // HDM Boot Protocol - Unified Path Service (PILLAR III)
            \App\Service\PathServiceInterface::class => \App\Service\UnifiedPathServiceFactory::class,
            
            // Legacy aliases for backward compatibility - use factory

            \Mezzio\Template\TemplateRendererInterface::class => \Mezzio\LaminasView\LaminasViewRendererFactory::class,

            // Database services
            'pdo.user' => \App\Database\PdoFactory::class,
            'pdo.mark' => \App\Database\PdoFactory::class,
            'pdo.system' => \App\Database\PdoFactory::class,
            \App\Database\MigrationService::class => \App\Database\MigrationServiceFactory::class,

            // Flysystem services
            'flysystem.public.filesystem' => function (ContainerInterface $container): Filesystem {
                $config = $container->get('config');
                assert(is_array($config) && isset($config['paths']['public']) && is_string($config['paths']['public']));
                $adapter = new LocalFilesystemAdapter($config['paths']['public']);
                return new Filesystem($adapter);
            },
            'flysystem.themes.filesystem' => function (ContainerInterface $container): Filesystem {
                $config = $container->get('config');
                assert(is_array($config) && isset($config['paths']['themes']) && is_string($config['paths']['themes']));
                $adapter = new LocalFilesystemAdapter($config['paths']['themes']);
                return new Filesystem($adapter);
            },
            'flysystem.uploads.filesystem' => function (ContainerInterface $container): Filesystem {
                $config = $container->get('config');
                assert(is_array($config) && isset($config['paths']['uploads']) && is_string($config['paths']['uploads']));
                $adapter = new LocalFilesystemAdapter($config['paths']['uploads']);
                return new Filesystem($adapter);
            },
        ],
    ],
];
