<?php

declare(strict_types=1);

namespace Mark\Middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Service\UserRepository;

/**
 * HDM Boot Protocol - Mark Authentication Middleware
 * 
 * Ensures only mark users (mark, editor, supermark roles) can access mark routes
 * Separate from user authentication for security isolation
 */
class MarkAuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $request->getAttribute('session');

        if (!$session instanceof SessionInterface) {
            // Session not available, redirect to mark login
            return new RedirectResponse('/mark/login?error=session_required');
        }

        // Check if mark user is authenticated
        $markUserId = $session->get('mark_user_id');
        
        if (!$markUserId) {
            // Not authenticated as mark user, redirect to mark login
            return new RedirectResponse('/mark/login');
        }
        
        // Get mark user from database
        $markUser = $this->userRepository->findById((int) $markUserId);
        
        if (!$markUser || !$markUser->isActive()) {
            // Mark user not found or inactive, clear session and redirect
            $session->unset('mark_user_id');
            $session->unset('mark_user_roles');
            return new RedirectResponse('/mark/login');
        }
        
        // Verify user has mark roles
        $userRoles = $markUser->getRoles();
        $markRoles = ['mark', 'editor', 'supermark'];
        
        $hasMarkRole = false;
        foreach ($markRoles as $markRole) {
            if (in_array($markRole, $userRoles, true)) {
                $hasMarkRole = true;
                break;
            }
        }
        
        if (!$hasMarkRole) {
            // User doesn't have mark roles, deny access
            $session->unset('mark_user_id');
            $session->unset('mark_user_roles');
            return new RedirectResponse('/mark/login?error=insufficient_privileges');
        }
        
        // Store mark user and roles in request for handlers
        $request = $request->withAttribute('mark_user', $markUser);
        $request = $request->withAttribute('mark_user_roles', $userRoles);
        
        // Update last activity
        $session->set('mark_last_activity', time());
        
        return $handler->handle($request);
    }
}
