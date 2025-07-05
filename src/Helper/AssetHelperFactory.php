<?php

declare(strict_types=1);

namespace App\Helper;

use App\Service\PathServiceInterface;
use Psr\Container\ContainerInterface;

class AssetHelperFactory
{
    public function __invoke(ContainerInterface $container): AssetHelper
    {
        $pathService = $container->get(PathServiceInterface::class);
        assert($pathService instanceof PathServiceInterface);
        
        return new AssetHelper($pathService, '/themes');
    }
}
