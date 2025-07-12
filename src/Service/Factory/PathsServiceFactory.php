<?php

declare(strict_types=1);

namespace App\Service\Factory;

use Psr\Container\ContainerInterface;
use ResponsiveSk\Slim4Paths\Paths;

/**
 * Factory for creating Paths service with MezzioOrbit preset
 *
 * Uses the preset registered in config/paths.php
 */
class PathsServiceFactory
{
    public function __invoke(ContainerInterface $container): Paths
    {
        // Load paths configuration
        $configPath = dirname(__DIR__, 3) . '/config/paths.php';
        return require $configPath;
    }
}
