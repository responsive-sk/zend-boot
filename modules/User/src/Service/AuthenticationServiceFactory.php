<?php

declare(strict_types=1);

namespace User\Service;

use Psr\Container\ContainerInterface;

class AuthenticationServiceFactory
{
    public function __invoke(ContainerInterface $container): AuthenticationService
    {
        return new AuthenticationService(
            $container->get(UserRepository::class)
        );
    }
}
