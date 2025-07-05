#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Mark Database Initialization Script
 * 
 * HDM Boot Protocol Compliant - Mark System Database
 * Initializes mark.db with mark management tables and sample data
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\MarkMigration;

echo "🚀 HDM Boot Protocol - Mark Database Initialization\n";
echo "===================================================\n\n";

try {
    // Load configuration
    $config = require __DIR__ . '/../config/config.php';
    $dbConfig = $config['database']['mark'];
    
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
    
    echo "📊 Initializing Mark Database: {$dbPath}\n\n";
    
    // Run mark migration
    $migration = new MarkMigration($pdo);
    $migration->migrate();
    
    echo "\n✅ Mark database initialization completed successfully!\n";
    echo "📝 Mark system tables created:\n";
    echo "   - marks (mark entries with user relationships)\n";
    echo "   - mark_categories (mark categorization)\n";
    echo "   - mark_permissions (mark access control)\n\n";
    
} catch (Exception $e) {
    echo "❌ Error initializing mark database: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "🎯 Mark system ready for HDM Boot Protocol compliance!\n";
