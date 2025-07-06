<?php

declare(strict_types=1);

namespace App\Service;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;

/**
 * HDM Boot Protocol - Unified Path Service
 *
 * PILLAR III: Secure Path Resolution
 * Combines legacy PathService + HdmPathService functionality
 * Single source of truth for all path operations
 */
class UnifiedPathService implements PathServiceInterface
{
    /** @var array<string, string> */
    private array $paths;
    private Filesystem $publicFs;
    private Filesystem $themesFs;

    /**
     * @param array<string, mixed> $appConfig
     * @param Filesystem $uploadsFs Unused parameter kept for interface compatibility
     */
    public function __construct(
        array $appConfig,
        Filesystem $publicFs,
        Filesystem $themesFs,
        Filesystem $uploadsFs
    ) {
        $pathsConfig = $appConfig['paths'] ?? [];
        $this->paths = is_array($pathsConfig) ? $pathsConfig : [];
        $this->publicFs = $publicFs;
        $this->themesFs = $themesFs;
        // Note: $uploadsFs parameter kept for interface compatibility but not stored
        unset($uploadsFs); // Explicitly mark as unused to satisfy PHPStan

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
        return $this->resolvePath('var/storage', $filename);
    }

    /**
     * Get logs directory path
     */
    public function logs(string $filename = ''): string
    {
        return $this->resolvePath('var/logs', $filename);
    }

    /**
     * Get cache directory path
     */
    public function cache(string $filename = ''): string
    {
        return $this->resolvePath('var/cache', $filename);
    }

    /**
     * Get sessions directory path
     */
    public function sessions(string $filename = ''): string
    {
        return $this->resolvePath('var/sessions', $filename);
    }

    /**
     * Get uploads directory path
     */
    public function uploads(string $filename = ''): string
    {
        return $this->resolvePath('var/uploads', $filename);
    }

    /**
     * Get templates directory path
     */
    public function templates(string $filename = ''): string
    {
        return $this->resolvePath('templates', $filename);
    }

    /**
     * Get app templates directory path
     */
    public function appTemplates(string $filename = ''): string
    {
        return $this->resolvePath('templates/app', $filename);
    }

    /**
     * Get module templates directory path
     */
    public function moduleTemplates(string $module, string $namespace, string $filename = ''): string
    {
        return $this->resolvePath("modules/{$module}/templates/{$namespace}", $filename);
    }

    // ========================================
    // Legacy PathService Methods (Public/Themes)
    // ========================================

    /**
     * Get root path
     */
    public function getRootPath(): string
    {
        $rootPath = $this->paths['root'] ?? getcwd();
        if (is_string($rootPath)) {
            return $rootPath;
        }

        $currentDir = getcwd();
        return $currentDir !== false ? $currentDir : __DIR__;
    }

    /**
     * Get public path
     */
    public function getPublicPath(): string
    {
        return $this->paths['public'] ?? 'public';
    }

    /**
     * Get themes path
     */
    public function getThemesPath(): string
    {
        return $this->paths['themes'] ?? 'public/themes';
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
     * Read public file
     */
    public function readPublicFile(string $path): string
    {
        try {
            return $this->publicFs->read($path);
        } catch (FilesystemException $e) {
            throw new \RuntimeException("Unable to read public file: {$path}", 0, $e);
        }
    }

    /**
     * Read theme file
     */
    public function readThemeFile(string $path): string
    {
        try {
            return $this->themesFs->read($path);
        } catch (FilesystemException $e) {
            throw new \RuntimeException("Unable to read theme file: {$path}", 0, $e);
        }
    }

    /**
     * Check if public file exists
     */
    public function publicFileExists(string $path): bool
    {
        try {
            return $this->publicFs->fileExists($path);
        } catch (FilesystemException $e) {
            return false;
        }
    }

    /**
     * Check if theme file exists
     */
    public function themeFileExists(string $path): bool
    {
        try {
            return $this->themesFs->fileExists($path);
        } catch (FilesystemException $e) {
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
     * Resolve path safely with validation
     */
    private function resolvePath(string $basePath, string $filename = ''): string
    {
        // Get absolute base path
        $rootPath = $this->getRootPath();
        $fullBasePath = $rootPath . DIRECTORY_SEPARATOR . $basePath;

        // Normalize path
        $normalizedPath = realpath($fullBasePath) ?: $fullBasePath;

        // Ensure directory exists
        if (!is_dir($normalizedPath)) {
            mkdir($normalizedPath, 0755, true);
        }

        // If no filename, return base path
        if (empty($filename)) {
            return $normalizedPath;
        }

        // Sanitize filename and return full path
        $sanitizedFilename = $this->sanitizeFilename($filename);
        return $normalizedPath . DIRECTORY_SEPARATOR . $sanitizedFilename;
    }

    /**
     * Sanitize filename to prevent path traversal
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove path traversal attempts
        $filename = str_replace(['../', '..\\', '../', '..\\'], '', $filename);

        // Remove null bytes
        $filename = str_replace("\0", '', $filename);

        // Normalize directory separators
        $filename = str_replace('\\', '/', $filename);

        return ltrim($filename, '/');
    }

    /**
     * Ensure required directories exist
     */
    private function ensureDirectoriesExist(): void
    {
        $requiredDirs = [
            'var/storage',
            'var/logs',
            'var/cache',
            'var/sessions',
            'var/uploads',
            'templates',
            'templates/app'
        ];

        foreach ($requiredDirs as $dir) {
            $this->resolvePath($dir);
        }
    }

    /**
     * Generic path method for custom paths
     */
    public function path(string $relativePath): string
    {
        return $this->resolvePath($relativePath);
    }
}
