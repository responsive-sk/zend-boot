<?php

declare(strict_types=1);

namespace User\Handler;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use User\Service\UserRepository;

class AdminHandlerFactory
{
    public function __invoke(ContainerInterface $container): AdminHandler
    {
        return new AdminHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(UserRepository::class)
        );
    }
}
