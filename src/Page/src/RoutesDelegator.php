<?php

declare(strict_types=1);

namespace Light\Page;

use Light\Page\Handler\PageHandler;
use Mezzio\Application;
use Psr\Container\ContainerInterface;

use function assert;

class RoutesDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        $app = $callback();
        assert($app instanceof Application);

        $app->get('/page[/{action}]', [PageHandler::class], 'page');

        return $app;
    }
}
