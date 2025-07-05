<?php

declare(strict_types=1);

namespace User\Handler;

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LogoutHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get Mezzio session
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        if ($session) {
if ($session && is_object($session)) {
    // Clear all session data
    if (method_exists($session, 'clear')) {
        $session->clear();
    }

    // Regenerate session ID
    if (method_exists($session, 'regenerate')) {
        $session->regenerate();
    }
}
        return new RedirectResponse('/user/login');
    }
}
}
