<?php

declare(strict_types=1);

namespace Mark\Handler;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * HDM Boot Protocol - Mark Cache Clear Handler
 *
 * Cache clearing for mark users
 */
class CacheClearHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // TODO: Implement cache clearing
        return new RedirectResponse('/mark/cache');
    }
}
