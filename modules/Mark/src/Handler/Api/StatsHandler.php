<?php

declare(strict_types=1);

namespace Mark\Handler\Api;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * HDM Boot Protocol - Mark API Stats Handler
 *
 * API endpoint for system statistics
 */
class StatsHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // TODO: Implement stats collection
        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'users' => 0,
                'marks' => 0,
                'uptime' => '0 days',
            ],
        ]);
    }
}
