<?php

declare(strict_types=1);

namespace Mark\Handler;

use Mark\Service\MarkUserRepository;
use Mark\Service\SystemStatsService;
use Mezzio\Template\TemplateRendererInterface;
use Orbit\Service\OrbitManager;
use Psr\Container\ContainerInterface;

class DashboardHandlerFactory
{
    public function __invoke(ContainerInterface $container): DashboardHandler
    {
        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        $markUserRepository = $container->get(MarkUserRepository::class);
        assert($markUserRepository instanceof MarkUserRepository);

        $statsService = $container->get(SystemStatsService::class);
        assert($statsService instanceof SystemStatsService);

        $orbitManager = $container->get(OrbitManager::class);
        assert($orbitManager instanceof OrbitManager);

        return new DashboardHandler($template, $markUserRepository, $statsService, $orbitManager);
    }
}
