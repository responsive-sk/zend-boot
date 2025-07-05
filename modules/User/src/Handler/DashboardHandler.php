<?php

declare(strict_types=1);

namespace User\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DashboardHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get user from session (set by AuthenticationMiddleware)
        $user = $request->getAttribute(UserInterface::class);

        if (!$user || !($user instanceof UserInterface)) {
            return new RedirectResponse('/user/login');
        }

        // Get session for flash messages
        $session = $request->getAttribute('session');
$session = $request->getAttribute('session');
$flashSuccess = null;

// Validate session object and get flash message
if ($session && is_object($session) && method_exists($session, 'get')) {
    $flashSuccess = $session->get('flash_success');
    if ($flashSuccess && method_exists($session, 'unset')) {
        $session->unset('flash_success');
    }
}

        return new HtmlResponse($this->template->render('user::dashboard', [
            'title' => 'Dashboard',
            'user' => $user,
            'username' => $user->getIdentity(),
            'roles' => iterator_to_array($user->getRoles()),
            'userDetails' => $user->getDetails(),
            'flash_success' => $flashSuccess,
        ]));
    }
}
