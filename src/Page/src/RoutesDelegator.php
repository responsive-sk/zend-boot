<?php

declare(strict_types=1);

namespace Light\Page;

use Light\Page\Handler\PageHandler;
use Mezzio\Application;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function assert;
use function sprintf;

class RoutesDelegator
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        $app = $callback();
        assert($app instanceof Application);

        $routes = $container->get('config')['routes'] ?? [];
        foreach ($routes as $moduleName => $moduleRoutes) {
            foreach ($moduleRoutes as $routeUri => $templateName) {
                $app->get(
                    sprintf('/%s/%s', $moduleName, $routeUri),
                    [PageHandler::class],
                    sprintf('%s::%s', $moduleName, $templateName)
                );
            }
        }

        return $app;
    }
}
