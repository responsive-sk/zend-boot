<?php

declare(strict_types=1);

namespace App\Handler;

use App\Helper\AssetHelper;
use Psr\Container\ContainerInterface;

class MainDemoHandlerFactory
{
    public function __invoke(ContainerInterface $container): MainDemoHandler
    {
        return new MainDemoHandler(
            $container->get(AssetHelper::class)
        );
    }
}
