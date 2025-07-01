<?php

declare(strict_types=1);

namespace App\Helper;

use Psr\Container\ContainerInterface;

class AssetHelperFactory
{
    public function __invoke(ContainerInterface $container): AssetHelper
    {
        return new AssetHelper('/themes');
    }
}
