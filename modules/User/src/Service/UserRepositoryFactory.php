<?php

declare(strict_types=1);

namespace User\Service;

use Psr\Container\ContainerInterface;

class UserRepositoryFactory
{
    public function __invoke(ContainerInterface $container): UserRepository
    {
        return new UserRepository(
            $container->get('pdo.user')
        );
    }
}
