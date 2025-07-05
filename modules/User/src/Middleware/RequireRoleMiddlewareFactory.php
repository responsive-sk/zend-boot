<?php

declare(strict_types=1);

namespace User\Middleware;

use Mezzio\Authorization\AuthorizationInterface;
use Psr\Container\ContainerInterface;

class RequireRoleMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): RequireRoleMiddleware
    {
        $authorization = $container->get(AuthorizationInterface::class);
        assert($authorization instanceof AuthorizationInterface);

        return new RequireRoleMiddleware($authorization);
    }
}
