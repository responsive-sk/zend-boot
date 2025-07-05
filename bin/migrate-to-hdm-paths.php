#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * HDM Boot Protocol - Path Migration Script
 * 
 * Migrates from legacy data/ structure to HDM Boot Protocol var/ structure
 * PILLAR VI: Organized Directory Structure
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Service\HdmPathService;

echo "🚀 HDM Boot Protocol - Path Migration\n";
echo "====================================\n\n";

try {
    // Load configuration
    $config = require __DIR__ . '/../config/config.php';
    
    // Create HDM Path Service
    $container = require __DIR__ . '/../config/container.php';
    $hdmPaths = $container->get(\App\Service\HdmPathService::class);
    
    echo "📋 Migrating to HDM Boot Protocol directory structure...\n\n";
    
    // Create var/ directory structure
    echo "📁 Creating HDM Boot Protocol directories...\n";
    createDirectory($hdmPaths->storage());
    createDirectory($hdmPaths->logs());
    createDirectory($hdmPaths->cache());
    createDirectory($hdmPaths->sessions());
    createDirectory($hdmPaths->content());
    
    // Migrate databases
    echo "\n📊 Migrating databases to var/storage/...\n";
    migrateDatabases($hdmPaths);
    
    // Migrate cache
    echo "\n💾 Migrating cache to var/cache/...\n";
    migrateCache($hdmPaths);
    
    // Create .htaccess protection
    echo "\n🔒 Creating security protection...\n";
    createSecurityFiles($hdmPaths);
    
    // Update configuration
    echo "\n⚙️ Updating configuration...\n";
    updateDatabaseConfig($hdmPaths);
    
    echo "\n✅ HDM Boot Protocol migration completed successfully!\n\n";
    
    echo "📊 New directory structure:\n";
    echo "var/\n";
    echo "├── storage/     # Database files (user.db, mark.db, system.db)\n";
    echo "├── logs/        # Application logs\n";
    echo "├── cache/       # Cache files\n";
    echo "└── sessions/    # Session data\n\n";
    
    echo "content/         # Content files (Git-friendly)\n";
    echo "public/\n";
    echo "├── assets/      # CSS, JS, images\n";
    echo "└── uploads/     # User uploads\n\n";
    
    echo "🎯 Your application is now HDM Boot Protocol PILLAR VI compliant!\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

function createDirectory(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "  ✅ Created: " . basename($path) . "/\n";
    } else {
        echo "  ℹ️ Exists: " . basename($path) . "/\n";
    }
}

function migrateDatabases(HdmPathService $hdmPaths): void
{
    $databases = ['user.db', 'mark.db', 'system.db'];
    $legacyDataDir = dirname($hdmPaths->storage()) . '/../data';
    
    foreach ($databases as $db) {
        $legacyPath = $legacyDataDir . '/' . $db;
        $newPath = $hdmPaths->storage($db);
        
        if (file_exists($legacyPath)) {
            if (!file_exists($newPath)) {
                copy($legacyPath, $newPath);
                echo "  📊 Migrated: {$db}\n";
            } else {
                echo "  ℹ️ Already exists: {$db}\n";
            }
        } else {
            echo "  ⚠️ Not found: {$db} (will be created on next init)\n";
        }
    }
}

function migrateCache(HdmPathService $hdmPaths): void
{
    $legacyCacheDir = dirname($hdmPaths->cache()) . '/../data/cache';
    $newCacheDir = $hdmPaths->cache();
    
    if (is_dir($legacyCacheDir)) {
        $files = glob($legacyCacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = basename($file);
                $newPath = $newCacheDir . '/' . $filename;
                
                if (!file_exists($newPath)) {
                    copy($file, $newPath);
                    echo "  💾 Migrated cache: {$filename}\n";
                }
            }
        }
    } else {
        echo "  ℹ️ No legacy cache to migrate\n";
    }
}

function createSecurityFiles(HdmPathService $hdmPaths): void
{
    // Protect var/ directory
    $varDir = dirname($hdmPaths->storage());
    $htaccessContent = "# HDM Boot Protocol - Security Protection\nDeny from all\n";
    
    file_put_contents($varDir . '/.htaccess', $htaccessContent);
    echo "  🔒 Protected: var/\n";
    
    // Protect storage directory
    file_put_contents($hdmPaths->storage() . '/.htaccess', $htaccessContent);
    echo "  🔒 Protected: var/storage/\n";
    
    // Protect logs directory
    file_put_contents($hdmPaths->logs() . '/.htaccess', $htaccessContent);
    echo "  🔒 Protected: var/logs/\n";
}

function updateDatabaseConfig(HdmPathService $hdmPaths): void
{
    $configPath = dirname($hdmPaths->storage()) . '/../config/autoload/database.local.php';
    
    $newConfig = '<?php
// HDM Boot Protocol - Database Configuration
// Using var/storage/ paths (PILLAR VI compliant)

return [
    "database" => [
        "user" => [
            "driver" => "sqlite",
            "database" => "' . $hdmPaths->storage('user.db') . '",
        ],
        "mark" => [
            "driver" => "sqlite",
            "database" => "' . $hdmPaths->storage('mark.db') . '",
        ],
        "system" => [
            "driver" => "sqlite",
            "database" => "' . $hdmPaths->storage('system.db') . '",
        ],
    ],
];
';
    
    file_put_contents($configPath, $newConfig);
    echo "  ⚙️ Updated: database.local.php\n";
    echo "  ℹ️ Using HDM Boot Protocol storage() paths\n";
}
