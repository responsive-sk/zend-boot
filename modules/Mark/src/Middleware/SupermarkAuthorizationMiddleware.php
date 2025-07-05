<?php

declare(strict_types=1);

namespace Mark\Middleware;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mark\Entity\MarkUser;

/**
 * HDM Boot Protocol - Supermark Authorization Middleware
 *
 * Ensures only supermark users can access critical system functions
 * Additional security layer for sensitive operations
 */
class SupermarkAuthorizationMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $markUser = $request->getAttribute('mark_user');

        if (!$markUser instanceof MarkUser) {
            // Should not happen if MarkAuthenticationMiddleware ran first
            return new HtmlResponse('Access denied: Authentication required', 403);
        }

        // Check if user has supermark role
        if (!$markUser->isSupermark()) {
            return new HtmlResponse(
                $this->renderAccessDeniedPage($markUser),
                403
            );
        }

        // User is supermark, allow access
        return $handler->handle($request);
    }

    private function renderAccessDeniedPage(MarkUser $markUser): string
    {
        $username = htmlspecialchars($markUser->getUsername());
        $roles = implode(', ', $markUser->getRoles());

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - HDM Boot Protocol</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .error { color: #dc3545; }
        .info { color: #6c757d; margin-top: 20px; }
        .back-link { margin-top: 20px; }
        .back-link a { color: #007bff; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="error">🚫 Access Denied</h1>
        <p><strong>HDM Boot Protocol - Supermark Authorization Required</strong></p>
        
        <p>This operation requires <strong>supermark</strong> privileges.</p>
        
        <div class="info">
            <p><strong>Current User:</strong> {$username}</p>
            <p><strong>Current Roles:</strong> {$roles}</p>
            <p><strong>Required Role:</strong> supermark</p>
        </div>
        
        <p>Only users with <strong>supermark</strong> role can access:</p>
        <ul>
            <li>Database management</li>
            <li>System settings</li>
            <li>Backup management</li>
            <li>User deletion</li>
        </ul>
        
        <div class="back-link">
            <a href="/mark/dashboard">← Back to Mark Dashboard</a>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
