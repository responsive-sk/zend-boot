<?php

declare(strict_types=1);

namespace User\Middleware;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authorization\AuthorizationInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequireRoleMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthorizationInterface $authorization,
        private array $requiredRoles = []
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute(UserInterface::class);

        if (!$user) {
            return new HtmlResponse('Unauthorized: No user found', 401);
        }

        // Check if user has any of the required roles
        foreach ($this->requiredRoles as $role) {
            if ($this->authorization->isGranted($role, $request)) {
                return $handler->handle($request);
            }
        }

        return new HtmlResponse('Forbidden: Insufficient permissions', 403);
    }

    /**
     * Factory method to create middleware for specific role
     */
    public static function forRole(AuthorizationInterface $authorization, string $role): self
    {
        return new self($authorization, [$role]);
    }

    /**
     * Factory method to create middleware for multiple roles (OR logic)
     */
    public static function forRoles(AuthorizationInterface $authorization, array $roles): self
    {
        return new self($authorization, $roles);
    }
}
