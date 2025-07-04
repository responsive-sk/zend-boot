<?php

declare(strict_types=1);

require 'vendor/autoload.php';

// Build container
$container = require 'config/container.php';

try {
    echo "=== Authentication Debug ===\n";
    
    // Get services
    $userRepo = $container->get(\User\Service\UserRepository::class);
    $authService = $container->get(\User\Service\AuthenticationService::class);
    
    echo "1. Testing UserRepository...\n";
    $user = $userRepo->findByUsername('user');
    if ($user) {
        echo "   ✅ User found: " . $user->getUsername() . "\n";
        echo "   ✅ Email: " . $user->getEmail() . "\n";
        echo "   ✅ Active: " . ($user->isActive() ? 'Yes' : 'No') . "\n";
        echo "   ✅ Roles: " . implode(', ', $user->getRoles()) . "\n";
    } else {
        echo "   ❌ User not found\n";
        exit(1);
    }
    
    echo "\n2. Testing password verification...\n";
    $passwordValid = $user->verifyPassword('user123');
    echo "   Password 'user123': " . ($passwordValid ? '✅ Valid' : '❌ Invalid') . "\n";
    
    echo "\n3. Testing AuthenticationService...\n";
    $authenticatedUser = $authService->authenticate('user', 'user123');
    if ($authenticatedUser) {
        echo "   ✅ Authentication successful\n";
        echo "   ✅ Identity: " . $authenticatedUser->getIdentity() . "\n";
        echo "   ✅ Roles: " . implode(', ', iterator_to_array($authenticatedUser->getRoles())) . "\n";
    } else {
        echo "   ❌ Authentication failed\n";
    }
    
    echo "\n4. Testing with email...\n";
    $authenticatedUser2 = $authService->authenticate('user@example.com', 'user123');
    if ($authenticatedUser2) {
        echo "   ✅ Email authentication successful\n";
    } else {
        echo "   ❌ Email authentication failed\n";
    }
    
    echo "\n=== Debug Complete ===\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
