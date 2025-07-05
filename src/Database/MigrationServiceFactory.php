<?php

declare(strict_types=1);

namespace App\Database;

use Psr\Container\ContainerInterface;

class MigrationServiceFactory
{
    public function __invoke(ContainerInterface $container): MigrationService
    {
        return new MigrationService(
            $container->get('pdo.user'),
            $container->get('pdo.mark'),
            $container->get('pdo.system')
        );
    }
}
