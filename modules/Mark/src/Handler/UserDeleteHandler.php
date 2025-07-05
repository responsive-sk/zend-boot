<?php

declare(strict_types=1);

namespace Mark\Handler;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * HDM Boot Protocol - Mark User Delete Handler
 *
 * User deletion for mark users
 */
class UserDeleteHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // TODO: Implement user deletion
        return new RedirectResponse('/mark/users');
    }
}
