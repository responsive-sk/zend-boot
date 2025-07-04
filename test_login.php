<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Laminas\Diactoros\ServerRequest;
use Mezzio\Session\SessionMiddleware;

// Build container
$container = require 'config/container.php';

try {
    echo "=== Login Test ===\n";
    
    // Create a mock request with login data
    $request = new ServerRequest(
        [],
        [],
        '/user/login',
        'POST',
        'php://input',
        [],
        [],
        [],
        [
            'credential' => 'user',
            'password' => 'user123'
        ]
    );
    
    // Add session to request
    $sessionManager = $container->get(\Mezzio\Session\SessionPersistenceInterface::class);
    $session = $sessionManager->initializeSessionFromRequest($request);
    $request = $request->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, $session);
    
    echo "1. Testing SimpleAuthentication directly...\n";
    $simpleAuth = $container->get(\Mezzio\Authentication\AuthenticationInterface::class);
    $user = $simpleAuth->authenticate($request);
    
    if ($user) {
        echo "   ✅ Authentication successful\n";
        echo "   ✅ Identity: " . $user->getIdentity() . "\n";
        echo "   ✅ Roles: " . implode(', ', iterator_to_array($user->getRoles())) . "\n";
    } else {
        echo "   ❌ Authentication failed\n";
    }
    
    echo "\n2. Testing LoginHandler...\n";
    $loginHandler = $container->get(\User\Handler\LoginHandler::class);
    $response = $loginHandler->handle($request);
    
    echo "   Response status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 302) {
        echo "   ✅ Redirect to: " . $response->getHeaderLine('Location') . "\n";
    } else {
        echo "   Response body: " . $response->getBody()->getContents() . "\n";
    }
    
    echo "\n=== Test Complete ===\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
