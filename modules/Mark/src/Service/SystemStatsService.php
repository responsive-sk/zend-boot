<?php

declare(strict_types=1);

namespace Mark\Service;

use PDO;
use App\Service\HdmPathService;

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
        private HdmPathService $pathService
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
        $stmt = $this->userPdo->query('SELECT COUNT(*) FROM users');
        return (int) $stmt->fetchColumn();
    }

    private function getTotalMarks(): int
    {
        try {
            $stmt = $this->markPdo->query('SELECT COUNT(*) FROM marks');
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            // Table might not exist yet
            return 0;
        }
    }

    private function getDiskUsage(): float
    {
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
        
        $databases = [
            'user' => $this->pathService->storage('user.db'),
            'mark' => $this->pathService->storage('mark.db'),
            'system' => $this->pathService->storage('system.db'),
        ];
        
        foreach ($databases as $name => $path) {
            if (file_exists($path)) {
                $sizes[$name] = filesize($path);
            } else {
                $sizes[$name] = 0;
            }
        }
        
        return $sizes;
    }
}
