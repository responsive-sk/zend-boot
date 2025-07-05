<?php

declare(strict_types=1);

namespace User\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Form\RegistrationForm;
use User\Service\AuthenticationService;

class RegistrationHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template,
        private AuthenticationService $authService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $form = new RegistrationForm();
        $error = null;
        $success = null;

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $form->setData($data);

            if ($form->isValid()) {
                $validatedData = $form->getData();

                try {
                    // Register new user
                    $user = $this->authService->registerUser(
                        $validatedData['username'],
                        $validatedData['email'],
                        $validatedData['password']
                    );

                    // Add flash message
                    $session->set('flash_success', 'Registration successful! You can now log in.');

                    return new RedirectResponse('/user/login');
                } catch (\InvalidArgumentException $e) {
                    $error = $e->getMessage();
                } catch (\Exception $e) {
                    $error = 'Registration failed. Please try again.';
                }
            } else {
                $error = 'Please correct the errors below.';
            }
        }

        // Set CSRF token
        $csrfToken = $request->getAttribute('csrf_token', '');
        $form->get('csrf_token')->setValue($csrfToken);

        return new HtmlResponse($this->template->render('user::register', [
            'form' => $form,
            'error' => $error,
            'success' => $success,
            'title' => 'Register',
        ]));
    }
}
