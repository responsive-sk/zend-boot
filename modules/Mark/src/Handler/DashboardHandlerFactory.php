<?php

declare(strict_types=1);

namespace Mark\Handler;

use Psr\Container\ContainerInterface;
use Mezzio\Template\TemplateRendererInterface;
use Mark\Service\MarkUserRepository;
use Mark\Service\SystemStatsService;

class DashboardHandlerFactory
{
    public function __invoke(ContainerInterface $container): DashboardHandler
    {
        return new DashboardHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(MarkUserRepository::class),
            $container->get(SystemStatsService::class)
        );
    }
}
