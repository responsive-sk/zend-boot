<?php

declare(strict_types=1);

namespace Mark\Service;

use App\Service\PathServiceInterface;
use PDO;
use Psr\Container\ContainerInterface;

class SystemStatsServiceFactory
{
    public function __invoke(ContainerInterface $container): SystemStatsService
    {
        $userPdo = $container->get('pdo.user');
        assert($userPdo instanceof PDO);

        $markPdo = $container->get('pdo.mark');
        assert($markPdo instanceof PDO);

        $systemPdo = $container->get('pdo.system');
        assert($systemPdo instanceof PDO);

        $pathService = $container->get(PathServiceInterface::class);
        assert($pathService instanceof PathServiceInterface);

        return new SystemStatsService($userPdo, $markPdo, $systemPdo, $pathService);
    }
}
