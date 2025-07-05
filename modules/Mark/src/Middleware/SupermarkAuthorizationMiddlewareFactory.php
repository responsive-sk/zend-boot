<?php

declare(strict_types=1);

namespace Mark\Middleware;

use Psr\Container\ContainerInterface;

class SupermarkAuthorizationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): SupermarkAuthorizationMiddleware
    {
        return new SupermarkAuthorizationMiddleware();
    }
}
