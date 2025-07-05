<?php

declare(strict_types=1);

namespace Mark\Service;

use PDO;
use Psr\Container\ContainerInterface;

class MarkUserRepositoryFactory
{
    public function __invoke(ContainerInterface $container): MarkUserRepository
    {
        $markPdo = $container->get('pdo.mark');
        assert($markPdo instanceof PDO);

        return new MarkUserRepository($markPdo);
    }
}
