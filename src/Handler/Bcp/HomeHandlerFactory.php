<?php

declare(strict_types=1);

namespace App\Handler;

use App\Helper\AssetHelper;
use Psr\Container\ContainerInterface;

class HomeHandlerFactory
{
    public function __invoke(ContainerInterface $container): HomeHandler
    {
        $assetHelper = $container->get(AssetHelper::class);
        assert($assetHelper instanceof AssetHelper);

        return new HomeHandler($assetHelper);
    }
}
