<?php

declare(strict_types=1);

namespace Mark\Handler;

use Psr\Container\ContainerInterface;
use Mezzio\Template\TemplateRendererInterface;
use Mark\Service\MarkUserRepository;

class LoginHandlerFactory
{
    public function __invoke(ContainerInterface $container): LoginHandler
    {
        return new LoginHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(MarkUserRepository::class)
        );
    }
}
