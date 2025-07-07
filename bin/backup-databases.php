#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * HDM Boot Protocol - Database Backup Script
 *
 * Pravidelný backup script pre HDM Boot Protocol databázy
 * Spúšťať: denne cez cron
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Service\HdmPathService;

echo "💾 HDM Boot Protocol - Database Backup\n";
echo "=====================================\n\n";

try {
    $container = require __DIR__ . '/../config/container.php';
    $hdmPaths = $container->get(\App\Service\HdmPathService::class);

    $storageDir = $hdmPaths->storage();
    $backupDir = $storageDir . '/backups';

    // Create backup directory
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
        echo "📁 Created backup directory: {$backupDir}\n";
    }

    $timestamp = date('Y-m-d_H-i-s');
    $backupPath = $backupDir . "/backup_{$timestamp}";
    mkdir($backupPath, 0755, true);

    echo "📦 Creating backup: {$timestamp}\n\n";

    // Backup databases
    $databases = ['user.db', 'mark.db', 'system.db'];
    $backupSizes = [];

    foreach ($databases as $db) {
        $sourcePath = $storageDir . '/' . $db;
        $backupFilePath = $backupPath . '/' . $db;

        if (file_exists($sourcePath)) {
            copy($sourcePath, $backupFilePath);
            $size = filesize($backupFilePath);
            $backupSizes[$db] = $size;
            echo "✅ Backed up {$db} (" . formatBytes($size) . ")\n";
        } else {
            echo "⚠️ Database {$db} not found, skipping\n";
        }
    }

    // Create backup manifest
    $manifest = [
        'timestamp' => $timestamp,
        'date' => date('Y-m-d H:i:s'),
        'databases' => $backupSizes,
        'total_size' => array_sum($backupSizes),
        'hdm_protocol_version' => '1.0',
        'backup_type' => 'full'
    ];

    file_put_contents($backupPath . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
    echo "📋 Created backup manifest\n";

    // Create compressed archive using PHP (shared hosting compatible)
    $archivePath = $backupDir . "/hdm_backup_{$timestamp}.tar.gz";

    if (class_exists('ZipArchive')) {
        // Use ZIP instead of tar.gz for better shared hosting compatibility
        $zipPath = $backupDir . "/hdm_backup_{$timestamp}.zip";
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($backupPath),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($backupPath) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
            $archiveSize = filesize($zipPath);
            echo "📦 Created compressed backup: " . basename($zipPath) . " (" . formatBytes($archiveSize) . ")\n";

            // Remove uncompressed backup directory using PHP
            removeDirectory($backupPath);
            echo "🧹 Cleaned up temporary files\n";

            $archivePath = $zipPath; // Update for logging
        } else {
            echo "❌ Failed to create ZIP backup\n";
        }
    } else {
        echo "⚠️ ZipArchive not available, keeping uncompressed backup\n";
        $archivePath = null; // No archive created
    }

    // Clean old backups (keep last 7 days)
    $cleanedCount = cleanOldBackups($backupDir, 7);
    echo "🗑️ Removed {$cleanedCount} old backup files\n";

    // Log backup activity
    logBackupActivity($container, $manifest, $archivePath ?? null);

    echo "\n✅ Database backup completed successfully!\n";
    echo "📊 Total backup size: " . formatBytes($manifest['total_size']) . "\n";
    echo "📁 Backup location: {$backupDir}\n";
} catch (Exception $e) {
    echo "❌ Backup failed: " . $e->getMessage() . "\n";
    exit(1);
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

function cleanOldBackups(string $backupDir, int $daysToKeep): int
{
    $cutoffTime = time() - ($daysToKeep * 24 * 3600);
    $patterns = [
        $backupDir . '/hdm_backup_*.tar.gz',
        $backupDir . '/hdm_backup_*.zip'
    ];
    $count = 0;

    foreach ($patterns as $pattern) {
        foreach (glob($pattern) as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $count++;
            }
        }
    }

    return $count;
}

/**
 * Remove directory recursively (shared hosting compatible)
 */
function removeDirectory(string $dir): bool
{
    if (!is_dir($dir)) {
        return false;
    }

    $files = array_diff(scandir($dir), ['.', '..']);

    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            removeDirectory($path);
        } else {
            unlink($path);
        }
    }

    return rmdir($dir);
}

/**
 * @param array<string, mixed> $manifest
 */
function logBackupActivity($container, array $manifest, ?string $archivePath): void
{
    try {
        $systemPdo = $container->get('pdo.system');

        $context = [
            'backup_timestamp' => $manifest['timestamp'],
            'databases_backed_up' => array_keys($manifest['databases']),
            'total_size' => $manifest['total_size'],
            'archive_path' => $archivePath ? basename($archivePath) : null
        ];

        $stmt = $systemPdo->prepare('
            INSERT INTO system_logs (level, message, context, module, created_at)
            VALUES (?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            'info',
            'Database backup completed',
            json_encode($context),
            'backup',
            date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        // Ignore logging errors during backup
        echo "⚠️ Could not log backup activity: " . $e->getMessage() . "\n";
    }
}
