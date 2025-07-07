#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Shared Hosting Cleanup Script
 *
 * HDM Boot Protocol - Minimal cleanup for shared hosting environments
 * Compatible with disabled functions (no exec, disk_free_space, etc.)
 */

// Bootstrap the application
require_once __DIR__ . '/../vendor/autoload.php';

echo "🧹 HDM Boot Protocol - Shared Hosting Cleanup\n";
echo "=============================================\n\n";

try {
    // Get container and services
    $container = require __DIR__ . '/../config/container.php';
    $hdmPaths = $container->get(\App\Service\PathServiceInterface::class);

    $totalCleaned = 0;

    // Clean cache files
    echo "🗂️  Cleaning cache files...\n";
    $cacheDir = $hdmPaths->cache();
    $cacheCount = cleanDirectory($cacheDir, '*.cache');
    echo "   ✅ Removed {$cacheCount} cache files\n";
    $totalCleaned += $cacheCount;

    // Clean old session files (older than 24 hours)
    echo "🔐 Cleaning old session files...\n";
    $sessionsDir = $hdmPaths->sessions();
    $sessionCount = cleanOldFiles($sessionsDir, 'sess_*', 24 * 3600);
    echo "   ✅ Removed {$sessionCount} old session files\n";
    $totalCleaned += $sessionCount;

    // Clean old log files (older than 7 days for shared hosting)
    echo "📝 Cleaning old log files...\n";
    $logsDir = $hdmPaths->logs();
    $logCount = cleanOldFiles($logsDir, '*.log', 7 * 24 * 3600);
    echo "   ✅ Removed {$logCount} old log files\n";
    $totalCleaned += $logCount;

    // Clean temporary files
    echo "🗑️  Cleaning temporary files...\n";
    $tempCount = cleanDirectory($cacheDir, '*.tmp');
    echo "   ✅ Removed {$tempCount} temporary files\n";
    $totalCleaned += $tempCount;

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 Cleanup completed successfully!\n";
    echo "📊 Total files cleaned: {$totalCleaned}\n";
    echo "💾 Disk space freed up\n";
    echo "🚀 System optimized for shared hosting\n";

} catch (Exception $e) {
    echo "❌ Cleanup failed: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Clean files in directory by pattern
 */
function cleanDirectory(string $dir, string $pattern): int
{
    if (!is_dir($dir)) {
        return 0;
    }

    $files = glob($dir . '/' . $pattern);
    if (!is_array($files)) {
        return 0;
    }

    $count = 0;
    foreach ($files as $file) {
        if (is_file($file) && unlink($file)) {
            $count++;
        }
    }

    return $count;
}

/**
 * Clean old files by pattern and age
 */
function cleanOldFiles(string $dir, string $pattern, int $maxAge): int
{
    if (!is_dir($dir)) {
        return 0;
    }

    $files = glob($dir . '/' . $pattern);
    if (!is_array($files)) {
        return 0;
    }

    $cutoffTime = time() - $maxAge;
    $count = 0;

    foreach ($files as $file) {
        if (is_file($file) && filemtime($file) < $cutoffTime && unlink($file)) {
            $count++;
        }
    }

    return $count;
}
