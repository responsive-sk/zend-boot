<?php

declare(strict_types=1);

namespace Orbit\Factory;

use Orbit\Handler\BlogHandler;
use Orbit\Service\OrbitManager;
use Psr\Container\ContainerInterface;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Blog Handler Factory
 */
class BlogHandlerFactory
{
    public function __invoke(ContainerInterface $container): BlogHandler
    {
        $orbitManager = $container->get(OrbitManager::class);
        assert($orbitManager instanceof OrbitManager);

        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        return new BlogHandler($orbitManager, $template);
    }
}
