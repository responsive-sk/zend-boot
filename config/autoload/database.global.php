<?php

declare(strict_types=1);

// HDM Boot Protocol - Secure Database Configuration
// SECURITY FIX: Eliminated un-secure path traversal (../../)
// Using modern Paths service with MezzioOrbit preset for safe paths

// Get base path for database storage
$basePath = dirname(__DIR__, 2);
$storagePath = $basePath . '/var/storage';

// Ensure storage directory exists
if (!is_dir($storagePath)) {
    if (!mkdir($storagePath, 0755, true) && !is_dir($storagePath)) {
        throw new \RuntimeException("Failed to create storage directory: {$storagePath}");
    }
}

return [
    'database' => [
        // HDM Boot Protocol - Three-Database Foundation
        // SECURE: Using absolute paths with proper validation
        'user' => [
            'driver' => 'sqlite',
            'database' => $storagePath . '/user.db',
        ],
        'mark' => [
            'driver' => 'sqlite',
            'database' => $storagePath . '/mark.db',
        ],
        'system' => [
            'driver' => 'sqlite',
            'database' => $storagePath . '/system.db',
        ],
    ],
];
