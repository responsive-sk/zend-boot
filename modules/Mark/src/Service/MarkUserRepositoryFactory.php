<?php

declare(strict_types=1);

namespace Mark\Service;

use Psr\Container\ContainerInterface;

class MarkUserRepositoryFactory
{
    public function __invoke(ContainerInterface $container): MarkUserRepository
    {
        return new MarkUserRepository(
            $container->get('pdo.mark')
        );
    }
}
