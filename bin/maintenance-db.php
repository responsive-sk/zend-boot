#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * HDM Boot Protocol - Database Maintenance Script
 *
 * Pravidelný maintenance script pre optimalizáciu databáz
 * Spúšťať: týždenne cez cron
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔧 HDM Boot Protocol - Database Maintenance\n";
echo "==========================================\n\n";

try {
    $container = require __DIR__ . '/../config/container.php';

    // Get database connections
    $userPdo = $container->get('pdo.user');
    $markPdo = $container->get('pdo.mark');
    $systemPdo = $container->get('pdo.system');

    echo "📊 Starting database maintenance...\n\n";

    // Maintain user database
    echo "👤 User Database Maintenance:\n";
    $userStats = maintainDatabase($userPdo, 'user.db');
    displayStats($userStats);

    // Maintain mark database
    echo "\n📝 Mark Database Maintenance:\n";
    $markStats = maintainDatabase($markPdo, 'mark.db');
    displayStats($markStats);

    // Maintain system database
    echo "\n⚙️ System Database Maintenance:\n";
    $systemStats = maintainDatabase($systemPdo, 'system.db');
    displayStats($systemStats);

    // Clean old system logs from database
    echo "\n🧹 Cleaning old system logs...\n";
    $cleanedLogs = cleanOldSystemLogs($systemPdo, 30);
    echo "  🗑️ Removed {$cleanedLogs} old log entries (>30 days)\n";

    // Clean expired cache entries
    echo "\n💾 Cleaning expired cache entries...\n";
    $cleanedCache = cleanExpiredCacheEntries($systemPdo);
    echo "  🗑️ Removed {$cleanedCache} expired cache entries\n";

    // Clean old user sessions
    echo "\n🔐 Cleaning old user sessions...\n";
    $cleanedSessions = cleanOldUserSessions($userPdo, 7);
    echo "  🗑️ Removed {$cleanedSessions} old session records (>7 days)\n";

    echo "\n✅ Database maintenance completed successfully!\n";

    // Log maintenance activity
    logMaintenanceActivity($systemPdo, [
        'user_stats' => $userStats,
        'mark_stats' => $markStats,
        'system_stats' => $systemStats,
        'cleaned_logs' => $cleanedLogs,
        'cleaned_cache' => $cleanedCache,
        'cleaned_sessions' => $cleanedSessions
    ]);
} catch (Exception $e) {
    echo "❌ Database maintenance failed: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * @return array<string, mixed>
 */
function maintainDatabase(PDO $pdo, string $dbName): array
{
    $stats = ['database' => $dbName];

    // Get database size before
    $stats['size_before'] = getDatabaseSize($pdo);

    // Run VACUUM to reclaim space
    echo "  🔄 Running VACUUM...\n";
    $pdo->exec('VACUUM');

    // Run ANALYZE to update statistics
    echo "  📈 Running ANALYZE...\n";
    $pdo->exec('ANALYZE');

    // Check integrity
    echo "  🔍 Checking integrity...\n";
    $stmt = $pdo->query('PRAGMA integrity_check');
    $integrity = $stmt->fetchColumn();
    $stats['integrity'] = $integrity;

    // Get database size after
    $stats['size_after'] = getDatabaseSize($pdo);
    $stats['space_saved'] = $stats['size_before'] - $stats['size_after'];

    return $stats;
}

function getDatabaseSize(PDO $pdo): int
{
    $stmt = $pdo->query('PRAGMA page_count');
    $pageCount = $stmt->fetchColumn();

    $stmt = $pdo->query('PRAGMA page_size');
    $pageSize = $stmt->fetchColumn();

    return $pageCount * $pageSize;
}

/**
 * @param array<string, mixed> $stats
 */
function displayStats(array $stats): void
{
    $sizeBefore = formatBytes($stats['size_before']);
    $sizeAfter = formatBytes($stats['size_after']);
    $spaceSaved = formatBytes($stats['space_saved']);

    echo "  📊 Size before: {$sizeBefore}\n";
    echo "  📊 Size after: {$sizeAfter}\n";
    echo "  💾 Space saved: {$spaceSaved}\n";
    echo "  ✅ Integrity: {$stats['integrity']}\n";
}

function formatBytes(int $bytes): string
{
    if ($bytes >= 1024 * 1024) {
        return round($bytes / (1024 * 1024), 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' B';
}

function cleanOldSystemLogs(PDO $pdo, int $daysOld): int
{
    $cutoffDate = date('Y-m-d H:i:s', time() - ($daysOld * 24 * 3600));

    $stmt = $pdo->prepare('DELETE FROM system_logs WHERE created_at < ?');
    $stmt->execute([$cutoffDate]);

    return $stmt->rowCount();
}

function cleanExpiredCacheEntries(PDO $pdo): int
{
    $now = time();

    $stmt = $pdo->prepare('DELETE FROM cache WHERE expires_at IS NOT NULL AND expires_at < ?');
    $stmt->execute([$now]);

    return $stmt->rowCount();
}

function cleanOldUserSessions(PDO $pdo, int $daysOld): int
{
    $cutoffTime = time() - ($daysOld * 24 * 3600);

    $stmt = $pdo->prepare('DELETE FROM user_sessions WHERE last_activity < ?');
    $stmt->execute([$cutoffTime]);

    return $stmt->rowCount();
}

/**
 * @param array<string, mixed> $data
 */
function logMaintenanceActivity(PDO $pdo, array $data): void
{
    $stmt = $pdo->prepare('
        INSERT INTO system_logs (level, message, context, module, created_at)
        VALUES (?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        'info',
        'Database maintenance completed',
        json_encode($data),
        'maintenance',
        date('Y-m-d H:i:s')
    ]);
}
