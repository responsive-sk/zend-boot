<?php

declare(strict_types=1);

namespace Orbit\Factory;

use Orbit\Handler\PageHandler;
use Orbit\Service\OrbitManager;
use Psr\Container\ContainerInterface;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Page Handler Factory
 */
class PageHandlerFactory
{
    public function __invoke(ContainerInterface $container): PageHandler
    {
        $orbitManager = $container->get(OrbitManager::class);
        assert($orbitManager instanceof OrbitManager);

        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        return new PageHandler($orbitManager, $template);
    }
}
