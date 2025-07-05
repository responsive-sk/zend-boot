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
        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        $userRepository = $container->get(UserRepository::class);
        assert($userRepository instanceof UserRepository);

        return new AdminHandler($template, $userRepository);
    }
}
