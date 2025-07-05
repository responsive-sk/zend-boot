<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DebugHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        // Test session operations
        if ($session && is_object($session) && method_exists($session, 'set')) {
            $session->set('debug_test', 'Session is working!');
            $session->set('timestamp', date('Y-m-d H:i:s'));
        }

        $debug = [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'session_available' => $session ? 'YES' : 'NO',
            'session_data' => ($session && is_object($session) && method_exists($session, 'toArray')) ? $session->toArray() : 'N/A',
            'session_id' => ($session && is_object($session) && method_exists($session, 'getId')) ? $session->getId() : 'N/A',
            'cookies' => $request->getCookieParams(),
            'parsed_body' => $request->getParsedBody(),
        ];

        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Debug Info</title>
    <style>
        body { font-family: monospace; margin: 20px; }
        pre { background: #f5f5f5; padding: 10px; border: 1px solid #ddd; }
        .section { margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Debug Information</h1>
    
    <div class="section">
        <h2>Request Info</h2>
        <pre>' . htmlspecialchars(print_r($debug, true)) . '</pre>
    </div>
    
    <div class="section">
        <h2>Test Login Form</h2>
        <form method="POST" action="/user/login">
            <p>
                <label>Username: <input type="text" name="credential" value="user"></label>
            </p>
            <p>
                <label>Password: <input type="password" name="password" value="user123"></label>
            </p>
            <p>
                <button type="submit">Test Login</button>
            </p>
        </form>
    </div>
    
    <div class="section">
        <h2>Links</h2>
        <p><a href="/user/login">Login Page</a></p>
        <p><a href="/user/dashboard">Dashboard</a></p>
        <p><a href="/">Home</a></p>
    </div>
</body>
</html>';

        return new HtmlResponse($html);
    }
}
