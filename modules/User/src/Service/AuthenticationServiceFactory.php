<?php

declare(strict_types=1);

namespace User\Service;

use Psr\Container\ContainerInterface;

class AuthenticationServiceFactory
{
    public function __invoke(ContainerInterface $container): AuthenticationService
    {
        $userRepository = $container->get(UserRepository::class);
        assert($userRepository instanceof UserRepository);
        
        return new AuthenticationService($userRepository);
    }
}
