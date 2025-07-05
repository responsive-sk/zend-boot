<?php

declare(strict_types=1);

namespace Mark\Handler;

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * HDM Boot Protocol - Mark Logout Handler
 */
class LogoutHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute('session');
        
        if ($session instanceof SessionInterface) {
            // Clear mark session data
            $session->unset('mark_user_id');
            $session->unset('mark_user_roles');
            $session->unset('mark_last_activity');
            
            // Regenerate session ID for security
            $session->regenerate();
        }

        return new RedirectResponse('/mark/login?message=logged_out');
    }
}
