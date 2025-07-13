<?php

declare(strict_types=1);

namespace Orbit\Factory;

use Mezzio\Template\TemplateRendererInterface;
use Orbit\Handler\MarkDashboardHandler;
use Orbit\Service\OrbitManager;
use Psr\Container\ContainerInterface;

/**
 * Factory for MarkDashboardHandler
 */
class MarkDashboardHandlerFactory
{
    public function __invoke(ContainerInterface $container): MarkDashboardHandler
    {
        $orbitManager = $container->get(OrbitManager::class);
        assert($orbitManager instanceof OrbitManager);

        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        return new MarkDashboardHandler($orbitManager, $template);
    }
}
