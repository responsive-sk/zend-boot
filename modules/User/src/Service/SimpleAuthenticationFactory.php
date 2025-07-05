<?php

declare(strict_types=1);

namespace User\Service;

use Psr\Container\ContainerInterface;

class SimpleAuthenticationFactory
{
    public function __invoke(ContainerInterface $container): SimpleAuthentication
    {
        $authService = $container->get(AuthenticationService::class);
        assert($authService instanceof AuthenticationService);

        return new SimpleAuthentication($authService);
    }
}
