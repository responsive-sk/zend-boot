#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * HDM Boot Protocol - Cache Cleanup Script
 *
 * Pravidelný maintenance script pre čistenie cache súborov
 * Spúšťať: denne cez cron
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Service\HdmPathService;

echo "🧹 HDM Boot Protocol - Cache Cleanup\n";
echo "===================================\n\n";

try {
    $container = require __DIR__ . '/../config/container.php';
    $hdmPaths = $container->get(\App\Service\HdmPathService::class);

    $cacheDir = $hdmPaths->cache();
    $logsDir = $hdmPaths->logs();

    echo "📁 Cache directory: {$cacheDir}\n";
    echo "📁 Logs directory: {$logsDir}\n\n";

    // Clean expired cache files
    $expiredCount = cleanExpiredCache($cacheDir);
    echo "🗑️  Removed {$expiredCount} expired cache files\n";

    // Clean old log files (older than 30 days)
    $oldLogCount = cleanOldLogs($logsDir, 30);
    echo "📝 Removed {$oldLogCount} old log files (>30 days)\n";

    // Clean session files (older than 24 hours)
    $sessionsDir = $hdmPaths->sessions();
    $oldSessionCount = cleanOldSessions($sessionsDir, 24);
    echo "🔐 Removed {$oldSessionCount} old session files (>24h)\n";

    // Clean template cache (if exists)
    $templateCacheCount = cleanTemplateCache($cacheDir);
    echo "🎨 Removed {$templateCacheCount} old template cache files\n";

    echo "\n✅ Cache cleanup completed successfully!\n";

    // Log cleanup activity
    $logFile = $logsDir . '/cleanup.log';
    $logEntry = date('Y-m-d H:i:s') . " - Cache cleanup: {$expiredCount} cache, {$oldLogCount} logs, {$oldSessionCount} sessions, {$templateCacheCount} templates\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
} catch (Exception $e) {
    echo "❌ Cleanup failed: " . $e->getMessage() . "\n";
    exit(1);
}

function cleanExpiredCache(string $cacheDir): int
{
    $count = 0;
    $pattern = $cacheDir . '/*';

    foreach (glob($pattern) as $file) {
        if (is_file($file)) {
            // Check if file is older than 1 hour
            if (filemtime($file) < time() - 3600) {
                unlink($file);
                $count++;
            }
        }
    }

    return $count;
}

function cleanOldLogs(string $logsDir, int $daysOld): int
{
    $count = 0;
    $cutoffTime = time() - ($daysOld * 24 * 3600);
    $pattern = $logsDir . '/*.log';

    foreach (glob($pattern) as $file) {
        if (is_file($file) && basename($file) !== 'cleanup.log') {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $count++;
            }
        }
    }

    return $count;
}

function cleanOldSessions(string $sessionsDir, int $hoursOld): int
{
    $count = 0;
    $cutoffTime = time() - ($hoursOld * 3600);
    $pattern = $sessionsDir . '/sess_*';

    foreach (glob($pattern) as $file) {
        if (is_file($file)) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $count++;
            }
        }
    }

    return $count;
}

function cleanTemplateCache(string $cacheDir): int
{
    $count = 0;
    $templateCacheDir = $cacheDir . '/templates';

    if (is_dir($templateCacheDir)) {
        $pattern = $templateCacheDir . '/*';

        foreach (glob($pattern) as $file) {
            if (is_file($file)) {
                // Clean template cache older than 1 day
                if (filemtime($file) < time() - 86400) {
                    unlink($file);
                    $count++;
                }
            }
        }
    }

    return $count;
}
