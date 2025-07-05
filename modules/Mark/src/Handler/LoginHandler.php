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

    private function renderLoginForm(?string $error = null): string
    {
        $errorHtml = '';
        if ($error) {
            $errorHtml = '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Login - HDM Boot Protocol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #dc3545, #6f42c1); min-height: 100vh; }
        .login-container { min-height: 100vh; display: flex; align-items: center; }
        .login-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); }
        .mark-badge { background: linear-gradient(45deg, #dc3545, #6f42c1); }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="row justify-content-center w-100">
            <div class="col-md-6 col-lg-4">
                <div class="card login-card shadow-lg">
                    <div class="card-header text-center mark-badge text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-shield-alt me-2"></i>
                            HDM Boot Protocol
                        </h4>
                        <small>Mark System Access</small>
                    </div>
                    <div class="card-body p-4">
                        {$errorHtml}
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Login to Mark System
                            </button>
                        </form>
                        
                        <hr>
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Mark privileges required (mark, editor, supermark)
                            </small>
                        </div>
                        <div class="text-center mt-2">
                            <a href="/user/login" class="btn btn-outline-secondary btn-sm">
                                Regular User Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
HTML;
    }
}
