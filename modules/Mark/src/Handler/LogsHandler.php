<?php

declare(strict_types=1);

namespace Mark\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * HDM Boot Protocol - Mark Logs Handler
 *
 * Displays system logs for mark users
 */
class LogsHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new HtmlResponse($this->template->render('mark::logs', [
            'title' => 'System Logs',
            'logs' => [], // TODO: Implement log reading
        ]));
    }
}
