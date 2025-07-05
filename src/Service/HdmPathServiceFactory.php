<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Container\ContainerInterface;

/**
 * HDM Boot Protocol Compliant Path Service Factory
 */
class HdmPathServiceFactory
{
    public function __invoke(ContainerInterface $container): HdmPathService
    {
        $config = $container->get('config');
        assert(is_array($config));

        return new HdmPathService(
            $config,
            $container->get('flysystem.public.filesystem'),
            $container->get('flysystem.themes.filesystem'),
            $container->get('flysystem.uploads.filesystem')
        );
    }
}
