<?php

declare(strict_types=1);

namespace Mark\Handler;

use Mark\Service\SystemStatsService;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class HealthHandlerFactory
{
    public function __invoke(ContainerInterface $container): HealthHandler
    {
        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);
        
        $statsService = $container->get(SystemStatsService::class);
        assert($statsService instanceof SystemStatsService);
        
        return new HealthHandler($template, $statsService);
    }
}
