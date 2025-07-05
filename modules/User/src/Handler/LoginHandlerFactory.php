<?php

declare(strict_types=1);

namespace User\Handler;

use Mezzio\Authentication\Session\PhpSession;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class LoginHandlerFactory
{
    public function __invoke(ContainerInterface $container): LoginHandler
    {
        $template = $container->get(TemplateRendererInterface::class);
        $adapter = $container->get(PhpSession::class);

        assert($template instanceof TemplateRendererInterface);
        assert($adapter instanceof PhpSession);

        return new LoginHandler($template, $adapter);
    }
}
