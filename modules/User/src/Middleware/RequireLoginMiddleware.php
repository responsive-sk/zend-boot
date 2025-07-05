<?php

declare(strict_types=1);

namespace User\Middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequireLoginMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthenticationInterface $authentication,
        private string $redirectPath = '/user/login'
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->authentication->authenticate($request);

        if (!$user) {
            // Store the original URL for redirect after login
            $session = $request->getAttribute('session');
            if ($session) {
                $session->set('redirect_after_login', (string) $request->getUri());
            }

            return new RedirectResponse($this->redirectPath);
        }

        // Add user to request attributes for easy access in handlers
        $request = $request->withAttribute(UserInterface::class, $user);

        return $handler->handle($request);
    }
}
