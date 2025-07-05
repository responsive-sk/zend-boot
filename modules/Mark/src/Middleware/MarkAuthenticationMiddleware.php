<?php

declare(strict_types=1);

namespace Mark\Middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mark\Service\MarkUserRepository;

/**
 * HDM Boot Protocol - Mark Authentication Middleware
 *
 * Ensures only mark users (mark, editor, supermark roles) can access mark routes
 * Separate from user authentication for security isolation
 */
class MarkAuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private MarkUserRepository $markUserRepository
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

        // Get mark user from mark database
        if (!is_numeric($markUserId)) {
            $session->unset('mark_user_id');
            return new RedirectResponse('/mark/login');
        }

        $markUser = $this->markUserRepository->findById((int) $markUserId);

        if (!$markUser || !$markUser->isActive()) {
            // Mark user not found or inactive, clear session and redirect
            $session->unset('mark_user_id');
            $session->unset('mark_user_roles');
            return new RedirectResponse('/mark/login');
        }

        // Verify user is a mark user (all users in mark.db should be mark users)
        if (!$markUser->isMarkUser()) {
            // User doesn't have mark roles, deny access
            $session->unset('mark_user_id');
            $session->unset('mark_user_roles');
            return new RedirectResponse('/mark/login?error=insufficient_privileges');
        }

        $userRoles = $markUser->getRoles();

        // Store mark user and roles in request for handlers
        $request = $request->withAttribute('mark_user', $markUser);
        $request = $request->withAttribute('mark_user_roles', $userRoles);

        // Update last activity
        $session->set('mark_last_activity', time());

        return $handler->handle($request);
    }
}
