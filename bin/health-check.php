#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * HDM Boot Protocol - Health Check Script
 *
 * Pravidelný health check pre monitoring aplikácie
 * Spúšťať: každých 5 minút cez cron
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Service\HdmPathService;

$exitCode = 0;
$issues = [];

echo "🏥 HDM Boot Protocol - Health Check\n";
echo "==================================\n\n";

try {
    $container = require __DIR__ . '/../config/container.php';
    $hdmPaths = $container->get(\App\Service\HdmPathService::class);

    // Check database connections
    echo "📊 Database Health:\n";
    $dbHealth = checkDatabaseHealth($container);
    displayHealthStatus($dbHealth);
    if (!$dbHealth['healthy']) {
        $issues[] = 'Database connectivity issues';
        $exitCode = 1;
    }

    // Check file system
    echo "\n📁 File System Health:\n";
    $fsHealth = checkFileSystemHealth($hdmPaths);
    displayHealthStatus($fsHealth);
    if (!$fsHealth['healthy']) {
        $issues[] = 'File system issues';
        $exitCode = 1;
    }

    // Check disk space
    echo "\n💾 Disk Space Health:\n";
    $diskHealth = checkDiskSpace($hdmPaths);
    displayHealthStatus($diskHealth);
    if (!$diskHealth['healthy']) {
        $issues[] = 'Low disk space';
        $exitCode = 1;
    }

    // Check cache system
    echo "\n🗄️ Cache System Health:\n";
    $cacheHealth = checkCacheHealth($hdmPaths);
    displayHealthStatus($cacheHealth);
    if (!$cacheHealth['healthy']) {
        $issues[] = 'Cache system issues';
        $exitCode = 1;
    }

    // Check session system
    echo "\n🔐 Session System Health:\n";
    $sessionHealth = checkSessionHealth($hdmPaths);
    displayHealthStatus($sessionHealth);
    if (!$sessionHealth['healthy']) {
        $issues[] = 'Session system issues';
        $exitCode = 1;
    }

    // Overall status
    echo "\n" . str_repeat("=", 50) . "\n";
    if ($exitCode === 0) {
        echo "✅ Overall Health: HEALTHY\n";
        echo "🎯 All systems operational\n";
    } else {
        echo "❌ Overall Health: UNHEALTHY\n";
        echo "💥 Issues found:\n";
        foreach ($issues as $issue) {
            echo "   - {$issue}\n";
        }
    }

    // Log health check
    logHealthCheck($container, $exitCode === 0, $issues);
} catch (Exception $e) {
    echo "❌ Health check failed: " . $e->getMessage() . "\n";
    $exitCode = 2;
}

exit($exitCode);

/**
 * @return array<string, mixed>
 */
function checkDatabaseHealth($container): array
{
    $health = ['healthy' => true, 'details' => []];

    try {
        // Test user database
        $userPdo = $container->get('pdo.user');
        $stmt = $userPdo->query('SELECT COUNT(*) FROM users');
        $userCount = $stmt->fetchColumn();
        $health['details']['user_db'] = "✅ {$userCount} users";

        // Test mark database
        $markPdo = $container->get('pdo.mark');
        $stmt = $markPdo->query('SELECT COUNT(*) FROM marks');
        $markCount = $stmt->fetchColumn();
        $health['details']['mark_db'] = "✅ {$markCount} marks";

        // Test system database
        $systemPdo = $container->get('pdo.system');
        $stmt = $systemPdo->query('SELECT COUNT(*) FROM system_logs');
        $logCount = $stmt->fetchColumn();
        $health['details']['system_db'] = "✅ {$logCount} log entries";
    } catch (Exception $e) {
        $health['healthy'] = false;
        $health['details']['error'] = "❌ " . $e->getMessage();
    }

    return $health;
}

/**
 * @return array<string, mixed>
 */
function checkFileSystemHealth(HdmPathService $hdmPaths): array
{
    $health = ['healthy' => true, 'details' => []];

    $directories = [
        'storage' => $hdmPaths->storage(),
        'logs' => $hdmPaths->logs(),
        'cache' => $hdmPaths->cache(),
        'sessions' => $hdmPaths->sessions(),
        'public' => $hdmPaths->public()
    ];

    foreach ($directories as $name => $path) {
        if (is_dir($path) && is_writable($path)) {
            $health['details'][$name] = "✅ Writable";
        } else {
            $health['healthy'] = false;
            $health['details'][$name] = "❌ Not writable or missing";
        }
    }

    return $health;
}

/**
 * @return array<string, mixed>
 */
function checkDiskSpace(HdmPathService $hdmPaths): array
{
    $health = ['healthy' => true, 'details' => []];

    // Check if disk functions are available (some shared hosting providers disable them)
    if (!function_exists('disk_free_space') || !function_exists('disk_total_space')) {
        $health['details']['disk_space'] = "ℹ️ Disk space check unavailable (functions disabled)";
        return $health;
    }

    $rootPath = dirname($hdmPaths->storage());
    $freeBytes = disk_free_space($rootPath);
    $totalBytes = disk_total_space($rootPath);

    if ($freeBytes !== false && $totalBytes !== false) {
        $freePercent = ($freeBytes / $totalBytes) * 100;
        $freeMB = round($freeBytes / (1024 * 1024));

        if ($freePercent < 10) {
            $health['healthy'] = false;
            $health['details']['disk_space'] = "❌ Low space: {$freeMB}MB ({$freePercent}%)";
        } else {
            $health['details']['disk_space'] = "✅ Available: {$freeMB}MB ({$freePercent}%)";
        }
    } else {
        $health['healthy'] = false;
        $health['details']['disk_space'] = "❌ Cannot check disk space";
    }

    return $health;
}

/**
 * @return array<string, mixed>
 */
function checkCacheHealth(HdmPathService $hdmPaths): array
{
    $health = ['healthy' => true, 'details' => []];

    $cacheDir = $hdmPaths->cache();

    // Test cache write
    $testFile = $cacheDir . '/health_test_' . time();
    if (file_put_contents($testFile, 'test') !== false) {
        unlink($testFile);
        $health['details']['cache_write'] = "✅ Write test passed";
    } else {
        $health['healthy'] = false;
        $health['details']['cache_write'] = "❌ Write test failed";
    }

    // Count cache files
    $cacheFiles = glob($cacheDir . '/*');
    $cacheCount = is_array($cacheFiles) ? count($cacheFiles) : 0;
    $health['details']['cache_files'] = "ℹ️ {$cacheCount} cache files";

    return $health;
}

/**
 * @return array<string, mixed>
 */
function checkSessionHealth(HdmPathService $hdmPaths): array
{
    $health = ['healthy' => true, 'details' => []];

    $sessionsDir = $hdmPaths->sessions();

    // Test session directory
    if (is_dir($sessionsDir) && is_writable($sessionsDir)) {
        $health['details']['session_dir'] = "✅ Directory writable";
    } else {
        $health['healthy'] = false;
        $health['details']['session_dir'] = "❌ Directory not writable";
    }

    // Count session files
    $sessionFiles = glob($sessionsDir . '/sess_*');
    $sessionCount = is_array($sessionFiles) ? count($sessionFiles) : 0;
    $health['details']['active_sessions'] = "ℹ️ {$sessionCount} active sessions";

    return $health;
}

/**
 * @param array<string, mixed> $health
 */
function displayHealthStatus(array $health): void
{
    foreach ($health['details'] as $component => $status) {
        echo "  {$status}\n";
    }
}

/**
 * @param array<string> $issues
 */
function logHealthCheck($container, bool $healthy, array $issues): void
{
    try {
        $systemPdo = $container->get('pdo.system');

        $stmt = $systemPdo->prepare('
            INSERT INTO system_logs (level, message, context, module, created_at)
            VALUES (?, ?, ?, ?, ?)
        ');

        $level = $healthy ? 'info' : 'warning';
        $message = $healthy ? 'Health check passed' : 'Health check failed';
        $context = json_encode(['issues' => $issues]);

        $stmt->execute([$level, $message, $context, 'health_check', date('Y-m-d H:i:s')]);
    } catch (Exception $e) {
        // Ignore logging errors during health check
    }
}
