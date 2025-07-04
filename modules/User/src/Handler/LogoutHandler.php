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
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        
        if ($session) {
            // Clear user session data
            $session->unset('user');
            
            // Add flash message
            $session->set('flash_info', 'You have been logged out successfully.');
            
            // Regenerate session ID for security
            $session->regenerate();
        }

        return new RedirectResponse('/');
    }
}
