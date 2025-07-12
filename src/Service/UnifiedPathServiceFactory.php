<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Container\ContainerInterface;
use ResponsiveSk\Slim4Paths\Paths;

class UnifiedPathServiceFactory
{
    public function __invoke(ContainerInterface $container): UnifiedPathService
    {
        $paths = $container->get(Paths::class);
        assert($paths instanceof Paths);

        return new UnifiedPathService($paths);
    }
}
