<?php

declare(strict_types=1);

namespace Mark\Middleware;

use Psr\Container\ContainerInterface;
use Mark\Service\MarkUserRepository;

class MarkAuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): MarkAuthenticationMiddleware
    {
        return new MarkAuthenticationMiddleware(
            $container->get(MarkUserRepository::class)
        );
    }
}
