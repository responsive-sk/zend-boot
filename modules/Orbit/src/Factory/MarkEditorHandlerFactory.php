<?php

declare(strict_types=1);

namespace Orbit\Factory;

use Mezzio\Template\TemplateRendererInterface;
use Orbit\Handler\MarkEditorHandler;
use Orbit\Service\OrbitManager;
use Psr\Container\ContainerInterface;

/**
 * Factory for MarkEditorHandler
 */
class MarkEditorHandlerFactory
{
    public function __invoke(ContainerInterface $container): MarkEditorHandler
    {
        $orbitManager = $container->get(OrbitManager::class);
        assert($orbitManager instanceof OrbitManager);

        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        return new MarkEditorHandler($orbitManager, $template);
    }
}
