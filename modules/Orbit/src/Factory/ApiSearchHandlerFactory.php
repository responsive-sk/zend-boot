<?php

declare(strict_types=1);

namespace Orbit\Factory;

use Orbit\Handler\ApiSearchHandler;
use Orbit\Service\OrbitManager;
use Psr\Container\ContainerInterface;

/**
 * API Search Handler Factory
 */
class ApiSearchHandlerFactory
{
    public function __invoke(ContainerInterface $container): ApiSearchHandler
    {
        $orbitManager = $container->get(OrbitManager::class);
        assert($orbitManager instanceof OrbitManager);

        return new ApiSearchHandler($orbitManager);
    }
}
