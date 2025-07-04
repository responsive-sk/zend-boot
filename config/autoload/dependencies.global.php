<?php

declare(strict_types=1);

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Psr\Container\ContainerInterface;

return [
    'dependencies' => [
        'factories' => [
            // HDM Boot Protocol - Path services
            \App\Service\PathService::class => \App\Service\PathServiceFactory::class,
            \App\Service\HdmPathService::class => \App\Service\HdmPathServiceFactory::class,

            \Mezzio\Template\TemplateRendererInterface::class => \App\Template\PhpRendererFactory::class,

            // Database services
            'pdo.user' => \App\Database\PdoFactory::class,
            'pdo.mark' => \App\Database\PdoFactory::class,
            'pdo.system' => \App\Database\PdoFactory::class,
            \App\Database\MigrationService::class => \App\Database\MigrationServiceFactory::class,

            // Flysystem services
            'flysystem.public.filesystem' => function (ContainerInterface $container): Filesystem {
                $config = $container->get('config');
                $adapter = new LocalFilesystemAdapter($config['paths']['public']);
                return new Filesystem($adapter);
            },
            'flysystem.themes.filesystem' => function (ContainerInterface $container): Filesystem {
                $config = $container->get('config');
                $adapter = new LocalFilesystemAdapter($config['paths']['themes']);
                return new Filesystem($adapter);
            },
            'flysystem.uploads.filesystem' => function (ContainerInterface $container): Filesystem {
                $config = $container->get('config');
                $adapter = new LocalFilesystemAdapter($config['paths']['uploads']);
                return new Filesystem($adapter);
            },
        ],
    ],
];
