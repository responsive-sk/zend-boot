<?php

declare(strict_types=1);

namespace Orbit\Factory;

use Orbit\Handler\MarkContentHandler;
use Orbit\Service\OrbitManager;
use Psr\Container\ContainerInterface;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Mark Content Handler Factory
 */
class MarkContentHandlerFactory
{
    public function __invoke(ContainerInterface $container): MarkContentHandler
    {
        $orbitManager = $container->get(OrbitManager::class);
        assert($orbitManager instanceof OrbitManager);

        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        return new MarkContentHandler($orbitManager, $template);
    }
}
