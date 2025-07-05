<?php

declare(strict_types=1);

namespace Mark\Middleware;

use Psr\Container\ContainerInterface;
use User\Service\UserRepository;

class MarkAuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): MarkAuthenticationMiddleware
    {
        return new MarkAuthenticationMiddleware(
            $container->get(UserRepository::class)
        );
    }
}
