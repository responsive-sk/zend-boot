<?php

declare(strict_types=1);

namespace App\Handler;

use App\Helper\AssetHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class HomeHandlerFactory
{
    public function __invoke(ContainerInterface $container): HomeHandler
    {
        $assetHelper = $container->get(AssetHelper::class);
        assert($assetHelper instanceof AssetHelper);

        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;
        assert($template instanceof TemplateRendererInterface || null === $template);

        return new HomeHandler($assetHelper, $template);
    }
}
