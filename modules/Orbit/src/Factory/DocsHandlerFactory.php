<?php

declare(strict_types=1);

namespace Orbit\Factory;

use Orbit\Handler\DocsHandler;
use Orbit\Service\OrbitManager;
use Psr\Container\ContainerInterface;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Docs Handler Factory
 */
class DocsHandlerFactory
{
    public function __invoke(ContainerInterface $container): DocsHandler
    {
        $orbitManager = $container->get(OrbitManager::class);
        assert($orbitManager instanceof OrbitManager);

        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        return new DocsHandler($orbitManager, $template);
    }
}
