<?php

declare(strict_types=1);

namespace User\Service;

use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SimpleAuthentication implements AuthenticationInterface
{
    public function __construct(
        private AuthenticationService $authService
    ) {
    }

    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        if (!$session instanceof \Mezzio\Session\SessionInterface) {
            return null;
        }

        // Check if user is already authenticated in session
        $userData = $session->get('user');
        if (is_array($userData) && isset($userData['identity']) && is_string($userData['identity'])) {
            $user = $this->authService->findByCredential($userData['identity']);
            if ($user && $user->isActive()) {
                return new AuthenticatedUser($user);
            }
        }

        // Try to authenticate from request data
        $parsedBody = $request->getParsedBody();

        if (
            is_array($parsedBody) &&
            isset($parsedBody['credential'], $parsedBody['password']) &&
            is_string($parsedBody['credential']) &&
            is_string($parsedBody['password'])
        ) {
            $authenticatedUser = $this->authService->authenticate($parsedBody['credential'], $parsedBody['password']);
            return $authenticatedUser;
        }

        return null;
    }

    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        return new \Laminas\Diactoros\Response\RedirectResponse('/user/login');
    }
}
