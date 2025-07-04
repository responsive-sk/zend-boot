<?php

declare(strict_types=1);

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Psr\Container\ContainerInterface;

return [
    'dependencies' => [
        'factories' => [
            \App\Service\PathService::class => \App\Service\PathServiceFactory::class,
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
