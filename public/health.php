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

// Database connectivity check
try {
    require_once __DIR__ . "/../vendor/autoload.php";
    // Load configuration
    $config = require __DIR__ . '/../config/config.php';
    $dbConfig = $config['database']['user'] ?? null;
    
    if ($dbConfig) {
        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $dbConfig['driver'],
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['database'],
            $dbConfig['charset'] ?? 'utf8mb4'
        );
        
        $pdo = new PDO(
            $dsn,
            $dbConfig['username'],
            $dbConfig['password'],
            $dbConfig['options'] ?? []
        );
        
        // Test query
        $stmt = $pdo->query('SELECT 1');
        $result = $stmt->fetchColumn();
        
        if ($result === 1) {
            $health['checks']['database'] = 'ok';
        } else {
            $health['checks']['database'] = 'error';
            $health['status'] = 'error';
        }
    } else {
        $health['checks']['database'] = 'not_configured';
        $health['status'] = 'warning';
    }
} catch (Exception $e) {
    $health['checks']['database'] = 'error';
    $health['status'] = 'error';
    $health['errors']['database'] = $e->getMessage();
}

// Session functionality check
try {
    if (session_start()) {
        $_SESSION['health_check'] = time();
        $health['checks']['session'] = 'ok';
        session_destroy();
    } else {
        $health['checks']['session'] = 'error';
        $health['status'] = 'error';
    }
} catch (Exception $e) {
    $health['checks']['session'] = 'error';
    $health['status'] = 'error';
    $health['errors']['session'] = $e->getMessage();
}

// File system checks
$dataDir = __DIR__ . '/../data';
$logsDir = __DIR__ . '/../logs';

// Check if data directory is writable
if (is_dir($dataDir) && is_writable($dataDir)) {
    $health['checks']['data_directory'] = 'ok';
} else {
    $health['checks']['data_directory'] = 'error';
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
