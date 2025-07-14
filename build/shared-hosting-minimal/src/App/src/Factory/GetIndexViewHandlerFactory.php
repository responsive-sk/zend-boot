<?php

declare(strict_types=1);

namespace Light\App\Factory;

use Light\App\Handler\GetIndexViewHandler;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ResponsiveSk\Slim4Paths\Paths;

use function assert;

class GetIndexViewHandlerFactory
{
    /**
     * @param class-string $requestedName
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $requestedName): GetIndexViewHandler
    {
        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        $paths = $container->get(Paths::class);
        assert($paths instanceof Paths);

        return new GetIndexViewHandler($template, $paths);
    }
}
