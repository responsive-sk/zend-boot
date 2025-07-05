<?php

declare(strict_types=1);

namespace User\Handler;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use User\Service\AuthenticationService;

class RegistrationHandlerFactory
{
    public function __invoke(ContainerInterface $container): RegistrationHandler
    {
        return new RegistrationHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(AuthenticationService::class)
        );
    }
}
