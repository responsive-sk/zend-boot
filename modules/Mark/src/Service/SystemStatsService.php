<?php

declare(strict_types=1);

namespace Mark\Service;

use PDO;
use App\Service\PathServiceInterface;

/**
 * HDM Boot Protocol - System Statistics Service
 *
 * Provides system statistics for mark dashboard
 */
class SystemStatsService
{
    public function __construct(
        private PDO $userPdo,
        private PDO $markPdo,
        private PDO $systemPdo,
        private PathServiceInterface $pathService
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getSystemStats(): array
    {
        return [
            'total_users' => $this->getTotalUsers(),
            'total_marks' => $this->getTotalMarks(),
            'disk_usage' => $this->getDiskUsage(),
            'cache_files' => $this->getCacheFileCount(),
            'log_entries' => $this->getLogEntryCount(),
            'database_sizes' => $this->getDatabaseSizes(),
        ];
    }

    private function getTotalUsers(): int
    {
        // Count regular users from user.db
        $stmt = $this->userPdo->query('SELECT COUNT(*) FROM users');
        if ($stmt === false) {
            throw new \RuntimeException('Failed to execute user count query');
        }
        $regularUsers = (int) $stmt->fetchColumn();

        // Count mark users from mark.db
        try {
            $stmt = $this->markPdo->query('SELECT COUNT(*) FROM mark_users');
            if ($stmt === false) {
                $markUsers = 0;
            } else {
                $markUsers = (int) $stmt->fetchColumn();
            }
        } catch (\Exception $e) {
            $markUsers = 0;
        }

        return $regularUsers + $markUsers;
    }

    private function getTotalMarks(): int
    {
        try {
            $stmt = $this->markPdo->query('SELECT COUNT(*) FROM marks');
            if ($stmt === false) {
                return 0;
            }
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            // Table might not exist yet
            return 0;
        }
    }

    private function getDiskUsage(): float
    {
        // Check if disk functions are available (some shared hosting providers disable them)
        if (!function_exists('disk_free_space') || !function_exists('disk_total_space')) {
            return 0.0; // Return 0% usage when functions are not available
        }

        $rootPath = $this->pathService->storage();
        $freeBytes = disk_free_space($rootPath);
        $totalBytes = disk_total_space($rootPath);

        if ($freeBytes !== false && $totalBytes !== false) {
            $usedBytes = $totalBytes - $freeBytes;
            return round(($usedBytes / $totalBytes) * 100, 1);
        }

        return 0.0;
    }

    private function getCacheFileCount(): int
    {
        $cacheDir = $this->pathService->cache();
        $files = glob($cacheDir . '/*');
        return is_array($files) ? count($files) : 0;
    }

    private function getLogEntryCount(): int
    {
        try {
            $stmt = $this->systemPdo->query('SELECT COUNT(*) FROM system_logs');
            if ($stmt === false) {
                return 0;
            }
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            // Table might not exist yet
            return 0;
        }
    }

    /**
     * @return array<string, int>
     */
    private function getDatabaseSizes(): array
    {
        $sizes = [];

        // Use actual database paths (currently in data/ directory)
        $rootPath = dirname($this->pathService->storage());
        $databases = [
            'user' => $rootPath . '/../data/user.db',
            'mark' => $rootPath . '/../data/mark.db',
            'system' => $rootPath . '/../data/system.db',
        ];

        foreach ($databases as $name => $path) {
            if (file_exists($path)) {
                $size = filesize($path);
                $sizes[$name] = $size !== false ? $size : 0;
            } else {
                $sizes[$name] = 0;
            }
        }

        return $sizes;
    }
}
