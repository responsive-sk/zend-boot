<?php

declare(strict_types=1);

namespace Mark\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Session\SessionInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mark\Service\MarkUserRepository;

/**
 * HDM Boot Protocol - Mark Login Handler
 * 
 * Separate login for mark users (mark, editor, supermark roles)
 */
class LoginHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template,
        private MarkUserRepository $markUserRepository
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute('session');
        
        if (!$session instanceof SessionInterface) {
            return new HtmlResponse('Session required', 500);
        }

        // Check if already authenticated as mark user
        $markUserId = $session->get('mark_user_id');
        if ($markUserId) {
            return new RedirectResponse('/mark/dashboard');
        }

        if ($request->getMethod() === 'POST') {
            return $this->handleLoginAttempt($request, $session);
        }

        // Show login form
        $error = $request->getQueryParams()['error'] ?? null;
        
        return new HtmlResponse($this->template->render('mark::login', [
            'title' => 'Mark Login',
            'error' => $error,
        ]));
    }

    private function handleLoginAttempt(ServerRequestInterface $request, SessionInterface $session): ResponseInterface
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            return new HtmlResponse($this->template->render('mark::login', [
            'title' => 'Mark Login',
            'error' => 'Invalid request data',
        ]));
        }

        $username = is_string($body['username'] ?? null) ? $body['username'] : '';
        $password = is_string($body['password'] ?? null) ? $body['password'] : '';

        if (empty($username) || empty($password)) {
            return new HtmlResponse($this->template->render('mark::login', [
            'title' => 'Mark Login',
            'error' => 'Please enter username and password',
        ]));
        }

        // Find mark user in mark.db
        $user = $this->markUserRepository->findByUsername($username);
        
        if (!$user || !$user->isActive()) {
            return new HtmlResponse($this->template->render('mark::login', [
            'title' => 'Mark Login',
            'error' => 'Invalid credentials',
        ]));
        }

        // Verify password
        if (!password_verify($password, $user->getPasswordHash())) {
            return new HtmlResponse($this->template->render('mark::login', [
            'title' => 'Mark Login',
            'error' => 'Invalid credentials',
        ]));
        }

        // All users in mark.db should be mark users, but double-check
        if (!$user->isMarkUser()) {
            return new HtmlResponse($this->template->render('mark::login', [
            'title' => 'Mark Login',
            'error' => 'Access denied: Mark privileges required',
        ]));
        }

        $userRoles = $user->getRoles();

        // Login successful - set mark session
        $session->set('mark_user_id', $user->getId());
        $session->set('mark_user_roles', $userRoles);
        $session->set('mark_last_activity', time());

        // Update last login
        $user->setLastLoginAt(new \DateTimeImmutable());
        $this->markUserRepository->save($user);

        return new RedirectResponse('/mark/dashboard');
    }

}
