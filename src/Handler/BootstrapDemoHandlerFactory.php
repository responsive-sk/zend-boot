<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;

class BootstrapDemoHandlerFactory
{
    public function __invoke(ContainerInterface $container): BootstrapDemoHandler
    {
        return new BootstrapDemoHandler();
    }
}
