<?php

declare(strict_types=1);

namespace Light\App;

use Light\App\Handler\IndexHandler;
use Mezzio\Application;
use Psr\Container\ContainerInterface;

use function assert;

class RoutesDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        $app = $callback();
        assert($app instanceof Application);

        $app->get('/', [IndexHandler::class], 'app::index');

        return $app;
    }
}
