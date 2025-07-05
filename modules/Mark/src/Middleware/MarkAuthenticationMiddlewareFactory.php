<?php

declare(strict_types=1);

namespace Mark\Middleware;

use Mark\Service\MarkUserRepository;
use Psr\Container\ContainerInterface;

class MarkAuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): MarkAuthenticationMiddleware
    {
        $markUserRepository = $container->get(MarkUserRepository::class);
        assert($markUserRepository instanceof MarkUserRepository);
        
        return new MarkAuthenticationMiddleware($markUserRepository);
    }
}
