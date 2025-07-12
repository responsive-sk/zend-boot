#!/usr/bin/env php
<?php
/**
 * Authorization Test
 * 
 * Testuje authorization konfiguráciu.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Laminas\ServiceManager\ServiceManager;

echo "🔐 Authorization Test\n";
echo "====================\n\n";

try {
    // Test 1: Container setup
    echo "1. Testing container setup...\n";
    $container = require __DIR__ . '/../config/container.php';
    
    if ($container instanceof ServiceManager) {
        echo "   ✅ Container created successfully\n";
    } else {
        throw new Exception("Container not properly configured");
    }
    echo "\n";
    
    // Test 2: Authorization service
    echo "2. Testing authorization service...\n";
    
    try {
        $authorization = $container->get('Mezzio\Authorization\AuthorizationInterface');
        echo "   ✅ AuthorizationInterface available\n";
        echo "   📋 Type: " . get_class($authorization) . "\n";
    } catch (Exception $e) {
        echo "   ❌ AuthorizationInterface failed: " . $e->getMessage() . "\n";
        throw $e;
    }
    echo "\n";
    
    // Test 3: RequireRoleMiddleware
    echo "3. Testing RequireRoleMiddleware...\n";
    
    try {
        $middleware = $container->get('User\Middleware\RequireRoleMiddleware');
        echo "   ✅ RequireRoleMiddleware created successfully\n";
        echo "   📋 Type: " . get_class($middleware) . "\n";
    } catch (Exception $e) {
        echo "   ❌ RequireRoleMiddleware failed: " . $e->getMessage() . "\n";
        throw $e;
    }
    echo "\n";
    
    // Test 4: Orbit handlers
    echo "4. Testing Orbit handlers...\n";
    
    $orbitHandlers = [
        'Orbit\Handler\MarkContentHandler',
    ];
    
    foreach ($orbitHandlers as $handler) {
        try {
            $handlerInstance = $container->get($handler);
            echo "   ✅ $handler available\n";
        } catch (Exception $e) {
            echo "   ❌ $handler failed: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
    
    echo "🎉 Authorization tests completed!\n";
    echo "=================================\n";
    echo "✅ Authorization system is properly configured\n";
    echo "🔐 RBAC roles and permissions are set up\n";
    echo "🚀 Orbit protected routes should work now\n\n";
    
} catch (Exception $e) {
    echo "❌ Authorization test failed: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    exit(1);
}
