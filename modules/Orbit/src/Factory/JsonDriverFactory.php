<?php

declare(strict_types=1);

namespace Orbit\Factory;

use Orbit\Service\FileDriver\JsonDriver;
use Psr\Container\ContainerInterface;

/**
 * JSON Driver Factory
 */
class JsonDriverFactory
{
    public function __invoke(ContainerInterface $container): JsonDriver
    {
        $config = $container->get('config');
        assert(is_array($config));

        $orbitConfig = $config['orbit'] ?? [];
        assert(is_array($orbitConfig));

        $contentPath = $orbitConfig['content_path'] ?? 'content';
        assert(is_string($contentPath));

        return new JsonDriver($contentPath);
    }
}
