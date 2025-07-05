<?php

declare(strict_types=1);

namespace Mark\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * HDM Boot Protocol - Mark Edit Handler
 *
 * Mark user editing for supermark users
 */
class MarkEditHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            // TODO: Implement mark editing
            return new RedirectResponse('/mark/marks');
        }

        return new HtmlResponse($this->template->render('mark::mark-edit', [
            'title' => 'Edit Mark User',
            'mark' => null, // TODO: Load mark user
        ]));
    }
}
