<?php

declare(strict_types=1);

namespace App\Service;

use ResponsiveSk\Slim4Paths\Paths;

/**
 * HDM Boot Protocol - Unified Path Service
 *
 * PILLAR III: Secure Path Resolution
 * Modern wrapper around ResponsiveSk\Slim4Paths with custom MezzioOrbit preset
 * Maintains interface compatibility while using advanced path management
 */
class UnifiedPathService implements PathServiceInterface
{
    private Paths $paths;

    /**
     * @param Paths $paths Modern path service with MezzioOrbit preset
     */
    public function __construct(Paths $paths)
    {
        $this->paths = $paths;

        // Ensure required directories exist
        $this->ensureDirectoriesExist();
    }

    // ========================================
    // HDM Boot Protocol Storage Methods
    // ========================================

    /**
     * Get storage directory path (for databases, etc.)
     */
    public function storage(string $filename = ''): string
    {
        return $this->paths->getPath($this->paths->get('storage'), $filename);
    }

    /**
     * Get logs directory path
     */
    public function logs(string $filename = ''): string
    {
        return $this->paths->getPath($this->paths->get('logs'), $filename);
    }

    /**
     * Get cache directory path
     */
    public function cache(string $filename = ''): string
    {
        return $this->paths->getPath($this->paths->get('cache'), $filename);
    }

    /**
     * Get sessions directory path
     */
    public function sessions(string $filename = ''): string
    {
        return $this->paths->getPath($this->paths->get('sessions'), $filename);
    }

    /**
     * Get uploads directory path
     */
    public function uploads(string $filename = ''): string
    {
        return $this->paths->getPath($this->paths->get('uploads'), $filename);
    }

    /**
     * Get templates directory path
     */
    public function templates(string $filename = ''): string
    {
        return $this->paths->getPath($this->paths->get('templates'), $filename);
    }

    /**
     * Get app templates directory path
     */
    public function appTemplates(string $filename = ''): string
    {
        return $this->paths->getPath($this->paths->get('app_templates'), $filename);
    }

    /**
     * Get module templates directory path
     */
    public function moduleTemplates(string $module, string $namespace, string $filename = ''): string
    {
        $modulePath = $this->paths->getPath($this->paths->get('modules'), $module);
        return $this->paths->getPath($modulePath, "templates/{$namespace}/{$filename}");
    }

    // ========================================
    // Legacy PathService Methods (Public/Themes)
    // ========================================

    /**
     * Get root path
     */
    public function getRootPath(): string
    {
        return $this->paths->base();
    }

    /**
     * Get public path
     */
    public function getPublicPath(): string
    {
        return $this->paths->get('public');
    }

    /**
     * Get themes path
     */
    public function getThemesPath(): string
    {
        return $this->paths->get('themes');
    }

    /**
     * Get public file path
     */
    public function getPublicFilePath(string $relativePath): string
    {
        return $this->getPublicPath() . '/' . ltrim($relativePath, '/');
    }

    /**
     * Get theme file path
     */
    public function getThemeFilePath(string $relativePath): string
    {
        return $this->getThemesPath() . '/' . ltrim($relativePath, '/');
    }

    /**
     * Get upload file path
     */
    public function getUploadFilePath(string $relativePath): string
    {
        return $this->uploads(ltrim($relativePath, '/'));
    }

    /**
     * Read public file using Paths filesystem
     */
    public function readPublicFile(string $path): string
    {
        try {
            return $this->paths->readFile('public', $path);
        } catch (\Exception $e) {
            throw new \RuntimeException("Unable to read public file: {$path}", 0, $e);
        }
    }

    /**
     * Read theme file using Paths filesystem
     */
    public function readThemeFile(string $path): string
    {
        try {
            return $this->paths->readFile('themes', $path);
        } catch (\Exception $e) {
            throw new \RuntimeException("Unable to read theme file: {$path}", 0, $e);
        }
    }

    /**
     * Check if public file exists using Paths filesystem
     */
    public function publicFileExists(string $path): bool
    {
        try {
            return $this->paths->fileExists('public', $path);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if theme file exists using Paths filesystem
     */
    public function themeFileExists(string $path): bool
    {
        try {
            return $this->paths->fileExists('themes', $path);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get public URL
     */
    public function getPublicUrl(string $relativePath): string
    {
        return '/' . ltrim($relativePath, '/');
    }

    /**
     * Get theme URL
     */
    public function getThemeUrl(string $relativePath): string
    {
        return '/themes/' . ltrim($relativePath, '/');
    }

    // ========================================
    // Core Path Resolution Methods
    // ========================================



    /**
     * Ensure required directories exist
     */
    private function ensureDirectoriesExist(): void
    {
        $requiredDirs = [
            'storage',
            'logs',
            'cache',
            'sessions',
            'uploads',
            'templates',
            'app_templates'
        ];

        foreach ($requiredDirs as $dir) {
            $dirPath = $this->paths->get($dir);
            if (!is_dir($dirPath)) {
                if (!mkdir($dirPath, 0755, true) && !is_dir($dirPath)) {
                    throw new \RuntimeException("Failed to create directory: {$dirPath}");
                }
            }
        }
    }

    /**
     * Generic path method for custom paths
     */
    public function path(string $relativePath): string
    {
        return $this->paths->getPath($this->paths->base(), $relativePath);
    }
}
