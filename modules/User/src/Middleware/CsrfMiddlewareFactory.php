<?php

declare(strict_types=1);

namespace User\Middleware;

use Psr\Container\ContainerInterface;

class CsrfMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): CsrfMiddleware
    {
        return new CsrfMiddleware();
    }
}
