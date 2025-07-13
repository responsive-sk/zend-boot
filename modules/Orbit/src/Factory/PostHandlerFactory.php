<?php

declare(strict_types=1);

namespace Orbit\Factory;

use Orbit\Handler\PostHandler;
use Orbit\Service\OrbitManager;
use Psr\Container\ContainerInterface;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Post Handler Factory
 */
class PostHandlerFactory
{
    public function __invoke(ContainerInterface $container): PostHandler
    {
        $orbitManager = $container->get(OrbitManager::class);
        assert($orbitManager instanceof OrbitManager);

        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        return new PostHandler($orbitManager, $template);
    }
}
