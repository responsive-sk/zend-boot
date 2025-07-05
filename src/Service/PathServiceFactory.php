<?php

declare(strict_types=1);

namespace App\Service;

use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;

class PathServiceFactory
{
    public function __invoke(ContainerInterface $container): PathService
    {
        $config = $container->get('config');

        return new PathService(
            $config,
            $container->get('flysystem.public.filesystem'),
            $container->get('flysystem.themes.filesystem'),
            $container->get('flysystem.uploads.filesystem')
        );
    }
}
