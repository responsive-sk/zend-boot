<?php

declare(strict_types=1);

namespace App\Handler;

use App\Helper\AssetHelper;
use Psr\Container\ContainerInterface;

class HomeHandlerFactory
{
    public function __invoke(ContainerInterface $container): HomeHandler
    {
        return new HomeHandler(
            $container->get(AssetHelper::class)
        );
    }
}
