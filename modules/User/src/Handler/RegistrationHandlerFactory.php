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
        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        $authService = $container->get(AuthenticationService::class);
        assert($authService instanceof AuthenticationService);

        return new RegistrationHandler($template, $authService);
    }
}
