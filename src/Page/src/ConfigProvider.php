<?php

declare(strict_types=1);

namespace Light\Page;

use Light\Page\Factory\PageHandlerFactory;
use Light\Page\Factory\PageServiceFactory;
use Light\Page\Handler\PageHandler;
use Light\Page\RoutesDelegator;
use Light\Page\Service\PageService;
use Light\Page\Service\PageServiceInterface;
use Mezzio\Application;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class => [
                    RoutesDelegator::class,
                ],
            ],
            'factories'  => [
                PageHandler::class => PageHandlerFactory::class,
                PageService::class => PageServiceFactory::class,
            ],
            'aliases'    => [
                PageServiceInterface::class => PageService::class,
            ],
        ];
    }

    public function getTemplates(): array
    {
        return [
            'paths' => [
                'page' => [__DIR__ . '/../templates/page'],
            ],
        ];
    }
}
