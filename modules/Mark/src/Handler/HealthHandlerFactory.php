<?php

declare(strict_types=1);

namespace Mark\Handler;

use Psr\Container\ContainerInterface;
use Mezzio\Template\TemplateRendererInterface;
use Mark\Service\SystemStatsService;

class HealthHandlerFactory
{
    public function __invoke(ContainerInterface $container): HealthHandler
    {
        return new HealthHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(SystemStatsService::class)
        );
    }
}
