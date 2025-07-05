<?php

declare(strict_types=1);

namespace Mark\Handler;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * HDM Boot Protocol - Mark Backup Create Handler
 *
 * Backup creation for mark users
 */
class BackupCreateHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // TODO: Implement backup creation
        return new RedirectResponse('/mark/backup');
    }
}
