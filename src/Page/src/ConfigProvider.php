<?php

declare(strict_types=1);

namespace Light\Page;

use Light\Page\Factory\GetPageViewHandlerFactory;
use Light\Page\Factory\PageServiceFactory;
use Light\Page\Handler\GetPageViewHandler;
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
                GetPageViewHandler::class => GetPageViewHandlerFactory::class,
                PageService::class        => PageServiceFactory::class,
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
