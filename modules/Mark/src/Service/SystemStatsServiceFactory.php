<?php

declare(strict_types=1);

namespace Mark\Service;

use Psr\Container\ContainerInterface;
use App\Service\PathServiceInterface;

class SystemStatsServiceFactory
{
    public function __invoke(ContainerInterface $container): SystemStatsService
    {
        return new SystemStatsService(
            $container->get('pdo.user'),
            $container->get('pdo.mark'),
            $container->get('pdo.system'),
            $container->get(PathServiceInterface::class)
        );
    }
}
