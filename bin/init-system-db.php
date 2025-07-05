#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * System Database Initialization Script
 * 
 * HDM Boot Protocol Compliant - System Core Database
 * Initializes system.db with core system tables and configuration
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\SystemMigration;

echo "🚀 HDM Boot Protocol - System Database Initialization\n";
echo "=====================================================\n\n";

try {
    // Load configuration
    $config = require __DIR__ . '/../config/config.php';
    $dbConfig = $config['database']['system'];
    
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
    
    echo "📊 Initializing System Database: {$dbPath}\n\n";
    
    // Run system migration
    $migration = new SystemMigration($pdo);
    $migration->migrate();
    
    echo "\n✅ System database initialization completed successfully!\n";
    echo "⚙️ System core tables created:\n";
    echo "   - cache (application cache storage)\n";
    echo "   - system_logs (structured logging)\n";
    echo "   - template_cache (compiled templates)\n";
    echo "   - config_cache (configuration cache)\n";
    echo "   - system_settings (application settings)\n\n";
    
    echo "🔧 Default system settings configured:\n";
    echo "   - app_name: Mezzio User App\n";
    echo "   - app_version: 1.0.0\n";
    echo "   - maintenance_mode: disabled\n";
    echo "   - cache_enabled: enabled\n";
    echo "   - log_level: info\n";
    echo "   - session_timeout: 3600s\n\n";
    
} catch (Exception $e) {
    echo "❌ Error initializing system database: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "🎯 System core ready for HDM Boot Protocol compliance!\n";
