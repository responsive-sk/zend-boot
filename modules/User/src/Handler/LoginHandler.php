<?php

declare(strict_types=1);

namespace User\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Form\LoginForm;

class LoginHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template,
        private AuthenticationInterface $authentication
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        
        // Check if user is already logged in
        $user = $this->authentication->authenticate($request);
        if ($user) {
            return new RedirectResponse('/user/dashboard');
        }

        $form = new LoginForm();
        $error = null;

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $form->setData($data);

            if ($form->isValid()) {
                $validatedData = $form->getData();
                
                // Attempt authentication
                $user = $this->authentication->authenticate(
                    $request->withParsedBody([
                        'username' => $validatedData['credential'],
                        'password' => $validatedData['password'],
                    ])
                );

                if ($user) {
                    // Set session data
                    $session->set('user', [
                        'identity' => $user->getIdentity(),
                        'roles' => iterator_to_array($user->getRoles()),
                        'details' => $user->getDetails(),
                    ]);

                    // Add flash message
                    $session->set('flash_success', 'Welcome back, ' . $user->getIdentity() . '!');

                    // Redirect to intended page or dashboard
                    $redirectUrl = $session->get('redirect_after_login', '/user/dashboard');
                    $session->unset('redirect_after_login');
                    
                    return new RedirectResponse($redirectUrl);
                } else {
                    $error = 'Invalid credentials. Please try again.';
                }
            } else {
                $error = 'Please correct the errors below.';
            }
        }

        // Set CSRF token
        $csrfToken = $request->getAttribute('csrf_token', '');
        $form->get('csrf_token')->setValue($csrfToken);

        return new HtmlResponse($this->template->render('user::login', [
            'form' => $form,
            'error' => $error,
            'title' => 'Login',
        ]));
    }
}
