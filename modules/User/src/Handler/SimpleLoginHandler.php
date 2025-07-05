<?php

declare(strict_types=1);

namespace User\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Service\AuthenticationService;

class SimpleLoginHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template,
        private AuthenticationService $authService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Start PHP session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is already logged in
        if (isset($_SESSION['user_id'])) {
            return new RedirectResponse('/simple-dashboard');
        }

        $error = null;

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            if (empty($data['credential']) || empty($data['password'])) {
        
        // Validate that we have array data
        if (!is_array($data)) {
            $data = [];
        }
                $error = 'Please fill in all fields.';
            } else {
                // Attempt authentication
                $user = $this->authService->authenticate($data['credential'], $data['password']);

                if ($user) {
                    // Set session data using native PHP session
                    $_SESSION['user_id'] = $user->getDetail('id');
                    $_SESSION['username'] = $user->getIdentity();
                    $_SESSION['roles'] = iterator_to_array($user->getRoles());
                    $_SESSION['login_time'] = time();

                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    return new RedirectResponse('/simple-dashboard');
                } else {
                    $error = 'Invalid credentials. Please try again.';
                }
            }
        }

        return new HtmlResponse($this->template->render('user::simple-login', [
            'error' => $error,
            'title' => 'Simple Login',
        ]));
    }
}
