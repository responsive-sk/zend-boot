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

/**
 * Simple login handler for basic authentication
 */
class SimpleLoginHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template,
        private AuthenticationService $authService
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $error = null;

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            
            // Validate that we have array data
            if (!is_array($data)) {
                $data = [];
            }

            if (empty($data['credential']) || empty($data['password'])) {
                $error = 'Please fill in all fields.';
            } else {
                // Attempt authentication
                $user = $this->authService->authenticate($data['credential'], $data['password']);

                if ($user) {
                    // Set session data using native PHP session
                    $_SESSION['user_id'] = $user->getDetail('id');
                    $_SESSION['username'] = $user->getIdentity();
                    $_SESSION['roles'] = iterator_to_array($user->getRoles());

                    return new RedirectResponse('/user/dashboard');
                } else {
                    $error = 'Invalid credentials.';
                }
            }
        }

        return new HtmlResponse($this->template->render('user::simple-login', [
            'title' => 'Simple Login',
            'error' => $error,
        ]));
    }
}
