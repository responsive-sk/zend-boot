<?php

declare(strict_types=1);

namespace User\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SimpleDashboardHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Start PHP session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return new RedirectResponse('/simple-login');
        }

        return new HtmlResponse($this->template->render('user::simple-dashboard', [
            'title' => 'Dashboard',
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'roles' => $_SESSION['roles'] ?? [],
            'login_time' => $_SESSION['login_time'] ?? null,
        ]));
    }
}
