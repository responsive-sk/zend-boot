<?php

declare(strict_types=1);

namespace App\Service;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;

/**
 * HDM Boot Protocol Compliant Path Service
 * 
 * PILLAR III: Secure Path Resolution
 * Implements protocol-compliant methods for configured paths
 */
class HdmPathService
{
    /** @var array<string, string> */
    private array $paths;

    private Filesystem $publicFs;
    private Filesystem $themesFs;

    /**
     * @param array<string, mixed> $appConfig
     */
    public function __construct(
        array $appConfig,
        Filesystem $publicFilesystem,
        Filesystem $themesFilesystem,
        Filesystem $uploadsFilesystem // @phpstan-ignore-line unused parameter for interface compatibility
    ) {
        $paths = $appConfig['paths'] ?? [];
        assert(is_array($paths));
        $this->paths = $paths;
        $this->publicFs = $publicFilesystem;
        $this->themesFs = $themesFilesystem;
        
        $this->validateConfiguration();
    }

    // ✅ PROTOCOL COMPLIANT - Use specific methods for configured paths

    /**
     * Get storage path for database files
     * HDM Boot Protocol: Use storage() method for database files
     */
    public function storage(string $filename = ''): string
    {
        $storagePath = $this->paths['storage'] ?? $this->paths['root'] . '/var/storage';
        return $this->securePath($storagePath, $filename);
    }

    /**
     * Get logs path for log files
     * HDM Boot Protocol: Use logs() method for log files
     */
    public function logs(string $filename = ''): string
    {
        $logsPath = $this->paths['logs'] ?? $this->paths['root'] . '/var/logs';
        return $this->securePath($logsPath, $filename);
    }

    /**
     * Get cache path for cache files
     * HDM Boot Protocol: Use cache() method for cache files
     */
    public function cache(string $filename = ''): string
    {
        $cachePath = $this->paths['cache'] ?? $this->paths['root'] . '/var/cache';
        return $this->securePath($cachePath, $filename);
    }

    /**
     * Get sessions path for session files
     * HDM Boot Protocol: Use sessions() method for session files
     */
    public function sessions(string $filename = ''): string
    {
        $sessionsPath = $this->paths['sessions'] ?? $this->paths['root'] . '/var/sessions';
        return $this->securePath($sessionsPath, $filename);
    }

    /**
     * Get uploads path for user uploads
     * HDM Boot Protocol: Use uploads() method for user uploads
     */
    public function uploads(string $filename = ''): string
    {
        $uploadsPath = $this->paths['uploads'] ?? $this->paths['public'] . '/uploads';
        return $this->securePath($uploadsPath, $filename);
    }

    /**
     * Get themes path for theme files
     * HDM Boot Protocol: Use themes() method for theme files
     */
    public function themes(string $filename = ''): string
    {
        $themesPath = $this->paths['themes'] ?? $this->paths['public'] . '/themes';
        return $this->securePath($themesPath, $filename);
    }

    /**
     * Get public path for public assets
     * HDM Boot Protocol: Use public() method for public assets
     */
    public function public(string $filename = ''): string
    {
        $publicPath = $this->paths['public'] ?? $this->paths['root'] . '/public';
        return $this->securePath($publicPath, $filename);
    }

    /**
     * Get content path for content files
     * HDM Boot Protocol: Use content() method for content files
     */
    public function content(string $filename = ''): string
    {
        $contentPath = $this->paths['content'] ?? $this->paths['root'] . '/content';
        return $this->securePath($contentPath, $filename);
    }

    // ⚠️ LIMITED USE - Generic path method (uses basePath + relativePath)

    /**
     * Generic path method - use only for custom paths
     * HDM Boot Protocol: Avoid for configured paths, use specific methods instead
     */
    public function path(string $relativePath): string
    {
        $basePath = $this->paths['root'] ?? getcwd();
        if ($basePath === false) {
            throw new \RuntimeException('Unable to determine current working directory');
        }
        return $this->securePath($basePath, $relativePath);
    }

    // Security and validation methods

    /**
     * Secure path resolution with path traversal protection
     */
    private function securePath(string $basePath, string $filename): string
    {
        // Ensure base path exists
        if (!is_dir($basePath)) {
            mkdir($basePath, 0755, true);
        }

        // If no filename provided, return base path
        if (empty($filename)) {
            return realpath($basePath) ?: $basePath;
        }

        // Validate filename for path traversal
        if ($this->containsPathTraversal($filename)) {
            throw new \InvalidArgumentException("Path traversal detected in filename: {$filename}");
        }

        $fullPath = $basePath . DIRECTORY_SEPARATOR . $filename;
        
        // Ensure the resolved path is within the base path
        $realBasePath = realpath($basePath);
        $realFullPath = realpath(dirname($fullPath));
        
        if ($realBasePath && $realFullPath && !str_starts_with($realFullPath, $realBasePath)) {
            throw new \InvalidArgumentException("Path traversal detected: {$filename}");
        }

        return $fullPath;
    }

    /**
     * Check for path traversal patterns
     */
    private function containsPathTraversal(string $path): bool
    {
        $dangerousPatterns = ['../', '..\\', '../', '..\\'];
        
        foreach ($dangerousPatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Validate HDM Boot Protocol configuration
     */
    private function validateConfiguration(): void
    {
        $requiredPaths = ['root'];
        
        foreach ($requiredPaths as $pathKey) {
            if (!isset($this->paths[$pathKey])) {
                throw new \InvalidArgumentException("Required path '{$pathKey}' not configured");
            }
        }
    }

    // Legacy compatibility methods (deprecated)

    /**
     * @deprecated Use storage() method instead
     */
    public function getDatabasePath(string $filename): string
    {
        return $this->storage($filename);
    }

    /**
     * @deprecated Use public() method instead
     */
    public function getPublicFilePath(string $filename): string
    {
        return $this->public($filename);
    }

    /**
     * @deprecated Use themes() method instead
     */
    public function getThemeFilePath(string $filename): string
    {
        return $this->themes($filename);
    }

    // Filesystem operations (using Flysystem)

    /**
     * Read file content safely using Flysystem
     */
    public function readFile(string $path): string
    {
        try {
            if (str_starts_with($path, $this->paths['public'] ?? '')) {
                $relativePath = str_replace($this->paths['public'] . '/', '', $path);
                return $this->publicFs->read($relativePath);
            }
            
            if (str_starts_with($path, $this->paths['themes'] ?? '')) {
                $relativePath = str_replace($this->paths['themes'] . '/', '', $path);
                return $this->themesFs->read($relativePath);
            }
            
            // Fallback to direct file reading for other paths
            if (!file_exists($path)) {
                throw new \InvalidArgumentException("File not found: {$path}");
            }
            
            $content = file_get_contents($path);
            if ($content === false) {
                throw new \RuntimeException("Unable to read file: {$path}");
            }
            
            return $content;
            
        } catch (FilesystemException $e) {
            throw new \RuntimeException("Filesystem error: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Check if file exists safely
     */
    public function fileExists(string $path): bool
    {
        try {
            if (str_starts_with($path, $this->paths['public'] ?? '')) {
                $relativePath = str_replace($this->paths['public'] . '/', '', $path);
                return $this->publicFs->fileExists($relativePath);
            }
            
            return file_exists($path);
            
        } catch (FilesystemException $e) {
            return false;
        }
    }
}
