<?php

declare(strict_types=1);

/**
 * HDM Boot Protocol - Health Check Endpoint
 * 
 * This endpoint provides application health status for monitoring systems.
 * It checks database connectivity, session functionality, and other critical services.
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

$startTime = microtime(true);

$health = [
    'status' => 'ok',
    'timestamp' => date('c'),
    'version' => '2.0.1',
    'environment' => $_ENV['APP_ENV'] ?? 'unknown',
    'checks' => [],
    'metrics' => []
];

// Load dependencies and paths
require_once __DIR__ . "/../vendor/autoload.php";

// Load paths configuration
$paths = require __DIR__ . '/../config/paths.php';

// Database connectivity check
try {

    // Load configuration
    $config = require __DIR__ . '/../config/config.php';

    // SQLite Database connectivity check
    $userDbPath = $paths->getPath($paths->base(), $paths->get('user_db'));

    if (file_exists($userDbPath)) {
        try {
            $pdo = new PDO('sqlite:' . $userDbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Test query
            $stmt = $pdo->query('SELECT 1');
            if ($stmt !== false) {
                $result = $stmt->fetchColumn();

                if ($result === 1) {
                    $health['checks']['database'] = 'ok';
                } else {
                    $health['checks']['database'] = 'error';
                    $health['status'] = 'error';
                }
            } else {
                $health['checks']['database'] = 'error';
                $health['status'] = 'error';
            }
        } catch (PDOException $e) {
            $health['checks']['database'] = 'error';
            $health['status'] = 'error';
            $health['errors']['database'] = 'SQLite error: ' . $e->getMessage();
        }
    } else {
        $health['checks']['database'] = 'warning';
        $health['status'] = 'warning';
        $health['errors']['database'] = 'SQLite database file not found (will be created on first use)';
    }
} catch (PDOException $e) {
    $health['checks']['database'] = 'error';
    $health['status'] = 'error';
    $health['errors']['database'] = $e->getMessage();
}

// Session functionality check
if (session_start()) {
    $_SESSION['health_check'] = time();
    $health['checks']['session'] = 'ok';
    session_destroy();
} else {
    $health['checks']['session'] = 'error';
    $health['status'] = 'error';
}

// File system checks using Paths service
$varDir = $paths->getPath($paths->base(), $paths->get('var'));
$logsDir = $paths->getPath($paths->base(), $paths->get('logs'));
$storageDir = $paths->getPath($paths->base(), $paths->get('storage'));

// Check if var directory is writable
if (is_dir($varDir) && is_writable($varDir)) {
    $health['checks']['var_directory'] = 'ok';
} else {
    $health['checks']['var_directory'] = 'error';
    $health['status'] = 'error';
}

// Check if storage directory is writable
if (is_dir($storageDir) && is_writable($storageDir)) {
    $health['checks']['storage_directory'] = 'ok';
} else {
    $health['checks']['storage_directory'] = 'error';
    $health['status'] = 'error';
}

// Check if logs directory is writable
if (is_dir($logsDir) && is_writable($logsDir)) {
    $health['checks']['logs_directory'] = 'ok';
} else {
    $health['checks']['logs_directory'] = 'error';
    $health['status'] = 'error';
}

// PHP version check
$phpVersion = PHP_VERSION;
if (version_compare($phpVersion, '8.1.0', '>=')) {
    $health['checks']['php_version'] = 'ok';
} else {
    $health['checks']['php_version'] = 'warning';
    $health['status'] = 'warning';
}

// Required extensions check
$requiredExtensions = ['pdo', 'session', 'openssl', 'mbstring', 'json'];
$missingExtensions = [];

foreach ($requiredExtensions as $extension) {
    if (!extension_loaded($extension)) {
        $missingExtensions[] = $extension;
    }
}

if (empty($missingExtensions)) {
    $health['checks']['php_extensions'] = 'ok';
} else {
    $health['checks']['php_extensions'] = 'error';
    $health['status'] = 'error';
    $health['errors']['missing_extensions'] = $missingExtensions;
}

// Memory usage
$memoryUsage = memory_get_usage(true);
$memoryLimit = ini_get('memory_limit');
$memoryLimitBytes = $memoryLimit === '-1' ? PHP_INT_MAX : (int) $memoryLimit;

$health['metrics']['memory_usage'] = [
    'current' => $memoryUsage,
    'current_mb' => round($memoryUsage / 1024 / 1024, 2),
    'limit' => $memoryLimitBytes,
    'limit_mb' => $memoryLimit === '-1' ? 'unlimited' : round($memoryLimitBytes / 1024 / 1024, 2),
    'percentage' => $memoryLimit === '-1' ? 0 : round(($memoryUsage / $memoryLimitBytes) * 100, 2)
];

// Disk space check
$diskFree = disk_free_space(__DIR__);
$diskTotal = disk_total_space(__DIR__);

if ($diskFree !== false && $diskTotal !== false) {
    $diskUsagePercentage = round((($diskTotal - $diskFree) / $diskTotal) * 100, 2);
    
    $health['metrics']['disk_space'] = [
        'free' => $diskFree,
        'free_gb' => round($diskFree / 1024 / 1024 / 1024, 2),
        'total' => $diskTotal,
        'total_gb' => round($diskTotal / 1024 / 1024 / 1024, 2),
        'usage_percentage' => $diskUsagePercentage
    ];
    
    if ($diskUsagePercentage > 90) {
        $health['checks']['disk_space'] = 'error';
        $health['status'] = 'error';
    } elseif ($diskUsagePercentage > 80) {
        $health['checks']['disk_space'] = 'warning';
        if ($health['status'] === 'ok') {
            $health['status'] = 'warning';
        }
    } else {
        $health['checks']['disk_space'] = 'ok';
    }
}

// Response time
$endTime = microtime(true);
$health['metrics']['response_time_ms'] = round(($endTime - $startTime) * 1000, 2);

// Set HTTP status code based on health status
switch ($health['status']) {
    case 'ok':
        http_response_code(200);
        break;
    case 'warning':
        http_response_code(200); // Still OK but with warnings
        break;
    case 'error':
        http_response_code(500);
        break;
    default:
        http_response_code(500);
}

// Output health status
echo json_encode($health, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
