<?php

declare(strict_types=1);

namespace App\Factory;

use App\Service\HdmPathService;

/**
 * HDM Boot Protocol - Database Configuration Factory
 * 
 * Provides secure database paths using HdmPathService
 * Eliminates un-secure path traversal (../../)
 */
class DatabaseConfigFactory
{
    private HdmPathService $pathService;

    public function __construct()
    {
        $this->pathService = new HdmPathService();
    }

    /**
     * Get secure database configuration
     * @return array<string, array<string, string>>
     */
    public function getConfig(): array
    {
        return [
            'database' => [
                // HDM Boot Protocol - Three-Database Foundation
                // SECURE: Using HdmPathService for safe path resolution
                'user' => [
                    'driver' => 'sqlite',
                    'database' => $this->pathService->storage('user.db'),
                ],
                'mark' => [
                    'driver' => 'sqlite',
                    'database' => $this->pathService->storage('mark.db'),
                ],
                'system' => [
                    'driver' => 'sqlite',
                    'database' => $this->pathService->storage('system.db'),
                ],
            ],
        ];
    }

    /**
     * Get user database path
     */
    public function getUserDatabasePath(): string
    {
        return $this->pathService->storage('user.db');
    }

    /**
     * Get mark database path
     */
    public function getMarkDatabasePath(): string
    {
        return $this->pathService->storage('mark.db');
    }

    /**
     * Get system database path
     */
    public function getSystemDatabasePath(): string
    {
        return $this->pathService->storage('system.db');
    }

    /**
     * Get all database paths
     * @return array<string, string>
     */
    public function getAllDatabasePaths(): array
    {
        return [
            'user' => $this->getUserDatabasePath(),
            'mark' => $this->getMarkDatabasePath(),
            'system' => $this->getSystemDatabasePath(),
        ];
    }
}
