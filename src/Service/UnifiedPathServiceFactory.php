<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Container\ContainerInterface;
use League\Flysystem\Filesystem;

class UnifiedPathServiceFactory
{
    public function __invoke(ContainerInterface $container): UnifiedPathService
    {
        $config = $container->get('config');
        assert(is_array($config));

        $publicFs = $container->get('flysystem.public.filesystem');
        assert($publicFs instanceof Filesystem);

        $themesFs = $container->get('flysystem.themes.filesystem');
        assert($themesFs instanceof Filesystem);

        $uploadsFs = $container->get('flysystem.uploads.filesystem');
        assert($uploadsFs instanceof Filesystem);

        return new UnifiedPathService(
            $config,
            $publicFs,
            $themesFs,
            $uploadsFs
        );
    }
}
