<?php

declare(strict_types=1);

namespace App\Handler;

use App\Helper\AssetHelper;
use Psr\Container\ContainerInterface;

class BootstrapDemoHandlerFactory
{
    public function __invoke(ContainerInterface $container): BootstrapDemoHandler
    {
        $assetHelper = $container->get(AssetHelper::class);
        assert($assetHelper instanceof AssetHelper);

        return new BootstrapDemoHandler($assetHelper);
    }
}
