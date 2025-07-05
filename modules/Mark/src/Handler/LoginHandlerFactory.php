<?php

declare(strict_types=1);

namespace Mark\Handler;

use Mark\Service\MarkUserRepository;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class LoginHandlerFactory
{
    public function __invoke(ContainerInterface $container): LoginHandler
    {
        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        $markUserRepository = $container->get(MarkUserRepository::class);
        assert($markUserRepository instanceof MarkUserRepository);

        return new LoginHandler($template, $markUserRepository);
    }
}
