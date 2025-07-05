<?php

declare(strict_types=1);

namespace User\Handler;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class DashboardHandlerFactory
{
    public function __invoke(ContainerInterface $container): DashboardHandler
    {
        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        return new DashboardHandler($template);
    }
}
