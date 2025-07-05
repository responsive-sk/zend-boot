<?php

declare(strict_types=1);

namespace Mark\Handler;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * HDM Boot Protocol - Mark Delete Handler
 *
 * Mark user deletion for supermark users
 */
class MarkDeleteHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // TODO: Implement mark deletion
        return new RedirectResponse('/mark/marks');
    }
}
