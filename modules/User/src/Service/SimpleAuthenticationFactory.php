<?php

declare(strict_types=1);

namespace User\Service;

use Psr\Container\ContainerInterface;

class SimpleAuthenticationFactory
{
    public function __invoke(ContainerInterface $container): SimpleAuthentication
    {
        return new SimpleAuthentication(
            $container->get(AuthenticationService::class)
        );
    }
}
