<?php

declare(strict_types=1);

namespace User\Middleware;

use Mezzio\Authorization\AuthorizationInterface;
use Psr\Container\ContainerInterface;

class RequireRoleMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): RequireRoleMiddleware
    {
        return new RequireRoleMiddleware(
            $container->get(AuthorizationInterface::class),
            ['admin'] // Default to admin role
        );
    }
}
