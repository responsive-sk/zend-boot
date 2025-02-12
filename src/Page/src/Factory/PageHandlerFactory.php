<?php

namespace Light\Page\Factory;

use Light\Page\Handler\PageHandler;
use Mezzio\Handler\NotFoundHandler;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Twig\Loader\FilesystemLoader;

class PageHandlerFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $requestedName): PageHandler
    {
        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        $notFoundHandler = $container->get(NotFoundHandler::class);
        assert($notFoundHandler instanceof NotFoundHandler);

        return new PageHandler($template, $notFoundHandler);
    }
}