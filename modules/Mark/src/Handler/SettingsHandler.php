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
 * HDM Boot Protocol - Mark Settings Handler
 * 
 * System settings management for mark users
 */
class SettingsHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            // TODO: Implement settings save
            return new RedirectResponse('/mark/settings');
        }

        return new HtmlResponse($this->template->render('mark::settings', [
            'title' => 'System Settings',
            'settings' => [], // TODO: Load settings
        ]));
    }
}
