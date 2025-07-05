#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * User Database Initialization Script
 * 
 * HDM Boot Protocol Compliant - User System Database
 * Initializes user.db with user management tables and default users
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\UserMigration;

echo "🚀 HDM Boot Protocol - User Database Initialization\n";
echo "====================================================\n\n";

try {
    // Load configuration
    $config = require __DIR__ . '/../config/config.php';
    $dbConfig = $config['database']['user'];
    
    // Ensure data directory exists
    $dbPath = $dbConfig['database'];
    $dbDir = dirname($dbPath);
    
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0755, true);
        echo "📁 Created data directory: {$dbDir}\n";
    }
    
    // Create PDO connection
    $pdo = new PDO("sqlite:{$dbPath}");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "📊 Initializing User Database: {$dbPath}\n\n";
    
    // Run user migration
    $migration = new UserMigration($pdo);
    $migration->migrate();
    
    echo "\n✅ User database initialization completed successfully!\n";
    echo "👤 Default users created:\n";
    echo "   - admin/admin123 (admin, user roles)\n";
    echo "   - user/user123 (user role)\n";
    echo "   - mark/mark123 (mark, user roles)\n\n";
    
} catch (Exception $e) {
    echo "❌ Error initializing user database: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "🎯 User system ready for HDM Boot Protocol compliance!\n";
