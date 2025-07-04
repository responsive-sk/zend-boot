<?php

declare(strict_types=1);

// Simple session test without container
session_start();

echo "=== Simple Session Test ===\n";
echo "Session ID: " . session_id() . "\n";
echo "Session status: " . session_status() . "\n";
echo "Session name: " . session_name() . "\n";

// Test session operations
$_SESSION['test'] = 'value';
echo "Session test value: " . $_SESSION['test'] . "\n";

// Show session configuration
echo "\nSession Configuration:\n";
echo "session.cookie_lifetime: " . ini_get('session.cookie_lifetime') . "\n";
echo "session.cookie_path: " . ini_get('session.cookie_path') . "\n";
echo "session.cookie_domain: " . ini_get('session.cookie_domain') . "\n";
echo "session.cookie_secure: " . ini_get('session.cookie_secure') . "\n";
echo "session.cookie_httponly: " . ini_get('session.cookie_httponly') . "\n";
echo "session.use_cookies: " . ini_get('session.use_cookies') . "\n";

echo "\n=== Test Complete ===\n";
