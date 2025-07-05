<?php

declare(strict_types=1);

namespace User\Handler;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use User\Service\AuthenticationService;

class SimpleLoginHandlerFactory
{
    public function __invoke(ContainerInterface $container): SimpleLoginHandler
    {
        return new SimpleLoginHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(AuthenticationService::class)
        );
    }
}
