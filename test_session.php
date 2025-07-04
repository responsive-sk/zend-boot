<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Laminas\Diactoros\ServerRequest;
use Mezzio\Session\SessionMiddleware;

// Build container
$container = require 'config/container.php';

try {
    echo "=== Session Test ===\n";
    
    // Test 1: Check if session services are available
    echo "1. Testing session services...\n";
    
    try {
        $sessionPersistence = $container->get(\Mezzio\Session\SessionPersistenceInterface::class);
        echo "   ✅ SessionPersistenceInterface available\n";
    } catch (\Exception $e) {
        echo "   ❌ SessionPersistenceInterface failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $csrfGuard = $container->get(\Mezzio\Csrf\CsrfGuardInterface::class);
        echo "   ✅ CsrfGuardInterface available\n";
    } catch (\Exception $e) {
        echo "   ❌ CsrfGuardInterface failed: " . $e->getMessage() . "\n";
    }
    
    // Test 2: Create a simple request and test session
    echo "\n2. Testing session creation...\n";
    $request = new ServerRequest();
    
    try {
        $session = $sessionPersistence->initializeSessionFromRequest($request);
        echo "   ✅ Session created successfully\n";
        
        // Test session operations
        $session->set('test', 'value');
        $value = $session->get('test');
        echo "   ✅ Session set/get works: " . $value . "\n";
        
    } catch (\Exception $e) {
        echo "   ❌ Session creation failed: " . $e->getMessage() . "\n";
    }
    
    // Test 3: Test CSRF token generation
    echo "\n3. Testing CSRF token...\n";
    try {
        $token = $csrfGuard->generateToken();
        echo "   ✅ CSRF token generated: " . substr($token, 0, 10) . "...\n";
        
        $isValid = $csrfGuard->validateToken($token);
        echo "   ✅ CSRF token validation: " . ($isValid ? 'VALID' : 'INVALID') . "\n";
        
    } catch (\Exception $e) {
        echo "   ❌ CSRF token failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Session Test Complete ===\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
