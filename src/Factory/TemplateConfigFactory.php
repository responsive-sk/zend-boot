<?php

declare(strict_types=1);

namespace App\Factory;

use App\Service\UnifiedPathService;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * HDM Boot Protocol - Template Configuration Factory
 * 
 * Provides secure template paths using UnifiedPathService
 * Eliminates un-secure path traversal (../../)
 */
class TemplateConfigFactory
{
    private UnifiedPathService $pathService;

    public function __construct()
    {
        // Create minimal config for UnifiedPathService
        $config = ['paths' => ['root' => getcwd()]];
        
        // Create dummy filesystems for constructor
        $dummyAdapter = new LocalFilesystemAdapter('.');
        $dummyFs = new Filesystem($dummyAdapter);
        
        $this->pathService = new UnifiedPathService($config, $dummyFs, $dummyFs, $dummyFs);
    }

    /**
     * Get secure template configuration
     * @return array<string, array<string, array<string>>>
     */
    public function getConfig(): array
    {
        return [
            'templates' => [
                'paths' => [
                    // Default templates path - SECURE
                    '' => [$this->pathService->templates()],
                    
                    // User module templates - SECURE
                    'user' => [$this->pathService->moduleTemplates('User', 'user')],
                    
                    // Mark module templates - SECURE
                    'mark' => [$this->pathService->moduleTemplates('Mark', 'mark')],
                    
                    // App templates - SECURE
                    'app' => [$this->pathService->appTemplates()],
                ],
            ],
        ];
    }

    /**
     * Get all template paths
     * @return array<string, array<string>>
     */
    public function getAllTemplatePaths(): array
    {
        return [
            'default' => [$this->pathService->templates()],
            'user' => [$this->pathService->moduleTemplates('User', 'user')],
            'mark' => [$this->pathService->moduleTemplates('Mark', 'mark')],
            'app' => [$this->pathService->appTemplates()],
        ];
    }

    /**
     * Get template path for specific namespace
     */
    public function getTemplatePath(string $namespace = ''): string
    {
        switch ($namespace) {
            case 'user':
                return $this->pathService->moduleTemplates('User', 'user');
            case 'mark':
                return $this->pathService->moduleTemplates('Mark', 'mark');
            case 'app':
                return $this->pathService->appTemplates();
            default:
                return $this->pathService->templates();
        }
    }
}
