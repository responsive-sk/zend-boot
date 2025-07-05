<?php

declare(strict_types=1);

namespace User\Middleware;

use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;

class RequireLoginMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): RequireLoginMiddleware
    {
        $authentication = $container->get(AuthenticationInterface::class);
        assert($authentication instanceof AuthenticationInterface);

        return new RequireLoginMiddleware($authentication);
    }
}
