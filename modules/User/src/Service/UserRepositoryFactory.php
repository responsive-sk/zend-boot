<?php

declare(strict_types=1);

namespace User\Service;

use PDO;
use Psr\Container\ContainerInterface;

class UserRepositoryFactory
{
    public function __invoke(ContainerInterface $container): UserRepository
    {
        $pdo = $container->get('pdo.user');
        assert($pdo instanceof PDO);
        
        return new UserRepository($pdo);
    }
}
