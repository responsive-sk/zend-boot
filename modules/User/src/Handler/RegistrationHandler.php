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
            
            // Ensure we have array data for form
            if (!is_array($data)) {
                $data = [];
            }
            $form->setData($data);

            if ($form->isValid()) {
                $validatedData = $form->getData();
            
            // Validate that we have array data
            if (!is_array($validatedData)) {
                throw new \RuntimeException('Form data must be an array');
            }
            
            // Validate required fields
            if (!isset($validatedData['username']) || !is_string($validatedData['username'])) {
                throw new \RuntimeException('Username is required and must be a string');
            }
            if (!isset($validatedData['email']) || !is_string($validatedData['email'])) {
                throw new \RuntimeException('Email is required and must be a string');
            }
            if (!isset($validatedData['password']) || !is_string($validatedData['password'])) {
                throw new \RuntimeException('Password is required and must be a string');
            }

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
