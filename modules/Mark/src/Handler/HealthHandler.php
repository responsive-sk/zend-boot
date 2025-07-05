<?php

declare(strict_types=1);

namespace Mark\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mark\Entity\MarkUser;
use Mark\Service\SystemStatsService;

/**
 * HDM Boot Protocol - Health Handler
 * 
 * System health monitoring for mark users
 */
class HealthHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template,
        private SystemStatsService $statsService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $markUser = $request->getAttribute('mark_user');
        
        if (!$markUser instanceof MarkUser) {
            return new HtmlResponse('Mark user not found in request', 500);
        }

        try {
            // Get comprehensive system health data
            $healthData = $this->getSystemHealthData();
            
            // Prepare template data
            $templateData = [
                'mark_user' => $markUser,
                'user_roles' => $markUser->getRoles(),
                'is_supermark' => $markUser->isSupermark(),
                'is_editor' => $markUser->isEditor(),
                'health_data' => $healthData,
                'title' => 'System Health - HDM Boot Protocol',
            ];
            
            return new HtmlResponse($this->template->render('mark::health', $templateData));
            
        } catch (\Exception $e) {
            return new HtmlResponse(
                'Health Check Error: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getSystemHealthData(): array
    {
        $stats = $this->statsService->getSystemStats();
        
        return [
            'overall_status' => $this->calculateOverallStatus($stats),
            'database_health' => $this->getDatabaseHealth($stats),
            'disk_health' => $this->getDiskHealth($stats),
            'cache_health' => $this->getCacheHealth($stats),
            'system_info' => $this->getSystemInfo(),
            'recommendations' => $this->getRecommendations($stats),
        ];
    }

    /**
     * @param array<string, mixed> $stats
     */
    private function calculateOverallStatus(array $stats): string
    {
        $diskUsage = $stats['disk_usage'] ?? 0;
        $totalUsers = $stats['total_users'] ?? 0;
        $logEntries = $stats['log_entries'] ?? 0;
        
        if ($diskUsage > 90) {
            return 'critical';
        } elseif ($diskUsage > 80 || $logEntries > 1000) {
            return 'warning';
        } elseif ($totalUsers > 0) {
            return 'healthy';
        } else {
            return 'unknown';
        }
    }

    /**
     * @param array<string, mixed> $stats
     * @return array<string, mixed>
     */
    /**
     * @param array<string, mixed> $stats
     * @return array<string, mixed>
     */
    private function getDatabaseHealth(array $stats): array
    {
        $dbSizes = $stats['database_sizes'] ?? [];
        assert(is_array($dbSizes));

        return [
            'user_db' => [
                'size' => $this->formatBytes(is_int($dbSizes['user'] ?? null) ? $dbSizes['user'] : 0),
                'status' => (is_int($dbSizes['user'] ?? null) ? $dbSizes['user'] : 0) > 0 ? 'active' : 'empty',
            ],
            'mark_db' => [
                'size' => $this->formatBytes(is_int($dbSizes['mark'] ?? null) ? $dbSizes['mark'] : 0),
                'status' => (is_int($dbSizes['mark'] ?? null) ? $dbSizes['mark'] : 0) > 0 ? 'active' : 'empty',
            ],
            'system_db' => [
                'size' => $this->formatBytes(is_int($dbSizes['system'] ?? null) ? $dbSizes['system'] : 0),
                'status' => (is_int($dbSizes['system'] ?? null) ? $dbSizes['system'] : 0) > 0 ? 'active' : 'empty',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $stats
     * @return array<string, mixed>
     */
    private function getDiskHealth(array $stats): array
    {
        $diskUsage = $stats['disk_usage'] ?? 0;
        
        return [
            'usage_percent' => $diskUsage,
            'status' => $diskUsage > 90 ? 'critical' : ($diskUsage > 80 ? 'warning' : 'healthy'),
            'free_space' => 100 - $diskUsage,
        ];
    }

    /**
     * @param array<string, mixed> $stats
     * @return array<string, mixed>
     */
    private function getCacheHealth(array $stats): array
    {
        $cacheFiles = $stats['cache_files'] ?? 0;
        
        return [
            'file_count' => $cacheFiles,
            'status' => $cacheFiles > 100 ? 'warning' : 'healthy',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'hdm_protocol_version' => '1.0',
        ];
    }

    /**
     * @param array<string, mixed> $stats
     * @return array<string>
     */
    private function getRecommendations(array $stats): array
    {
        $recommendations = [];
        
        $diskUsage = $stats['disk_usage'] ?? 0;
        if ($diskUsage > 80) {
            $recommendations[] = 'Consider cleaning up old files or expanding disk space';
        }
        
        $cacheFiles = $stats['cache_files'] ?? 0;
        if ($cacheFiles > 100) {
            $recommendations[] = 'Run cache cleanup to improve performance';
        }
        
        $logEntries = $stats['log_entries'] ?? 0;
        if ($logEntries > 1000) {
            $recommendations[] = 'Archive old log entries to maintain performance';
        }
        
        if (empty($recommendations)) {
            $recommendations[] = 'System is running optimally';
        }
        
        return $recommendations;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
