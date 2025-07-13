<?php

declare(strict_types=1);

namespace Orbit\Factory;

use App\Helper\AssetHelper;
use Orbit\Handler\BlogTailwindHandler;
use Orbit\Service\OrbitManager;
use Psr\Container\ContainerInterface;

class BlogTailwindHandlerFactory
{
    public function __invoke(ContainerInterface $container): BlogTailwindHandler
    {
        $orbitManager = $container->get(OrbitManager::class);
        assert($orbitManager instanceof OrbitManager);

        $assetHelper = $container->get(AssetHelper::class);
        assert($assetHelper instanceof AssetHelper);

        return new BlogTailwindHandler($orbitManager, $assetHelper);
    }
}
