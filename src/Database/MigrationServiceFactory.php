<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use Psr\Container\ContainerInterface;

class MigrationServiceFactory
{
    public function __invoke(ContainerInterface $container): MigrationService
    {
        $userPdo = $container->get('pdo.user');
        assert($userPdo instanceof PDO);

        $markPdo = $container->get('pdo.mark');
        assert($markPdo instanceof PDO);

        $systemPdo = $container->get('pdo.system');
        assert($systemPdo instanceof PDO);

        return new MigrationService($userPdo, $markPdo, $systemPdo);
    }
}
