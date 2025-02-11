<?php

declare(strict_types=1);

namespace Light\Page;

use Light\Page\Factory\AboutUsHandlerFactory;
use Light\Page\Factory\PageServiceFactory;
use Light\Page\Factory\WhoWeAreHandlerFactory;
use Light\Page\Handler\AboutUsHandler;
use Light\Page\Handler\WhoWeAreHandler;
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
                    \Light\App\RoutesDelegator::class,
                    RoutesDelegator::class,
                ],
            ],
            'factories'  => [
                AboutUsHandler::class  => AboutUsHandlerFactory::class,
                WhoWeAreHandler::class => WhoWeAreHandlerFactory::class,
                PageService::class     => PageServiceFactory::class,
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
