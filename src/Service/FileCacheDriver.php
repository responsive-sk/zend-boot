<?php

declare(strict_types=1);

namespace App\Service;

use Psr\SimpleCache\CacheInterface;
use App\Service\UnifiedPathService;

/**
 * HDM Boot Protocol - File Cache Driver
 *
 * High-performance file-based cache with namespace support
 * Optimized for HDM Boot Protocol structure
 */
class FileCacheDriver implements CacheInterface
{
    private string $cachePath;
    private array $config;
    private UnifiedPathService $paths;

    public function __construct(string $cachePath, array $config = [], ?UnifiedPathService $paths = null)
    {
        $this->cachePath = rtrim($cachePath, '/');
        $this->config = array_merge([
            'extension' => '.cache',
            'directory_level' => 1,
            'file_locking' => true,
            'serializer' => 'php',
        ], $config);

        $this->paths = $paths ?? new UnifiedPathService(new \ResponsiveSk\Slim4Paths\Paths(__DIR__ . '/../../'));

        // Ensure cache directory exists
        $this->ensureCacheDirectory();
    }

    public function get($key, $default = null)
    {
        $filename = $this->getFilename($key);

        if (!file_exists($filename)) {
            return $default;
        }

        $data = $this->readFile($filename);

        if ($data === null) {
            return $default;
        }

        // Check expiration
        if ($data['expires'] > 0 && $data['expires'] < time()) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    public function set($key, $value, $ttl = null)
    {
        $filename = $this->getFilename($key);
        $dir = dirname($filename);

        // Create directory if it doesn't exist
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $data = [
            'value' => $value,
            'expires' => $ttl ? (time() + $ttl) : 0,
            'created' => time(),
            'key' => $key,
        ];

        return $this->writeFile($filename, $data);
    }

    public function delete($key)
    {
        $filename = $this->getFilename($key);

        if (file_exists($filename)) {
            return unlink($filename);
        }

        return true;
    }

    public function clear()
    {
        return $this->clearDirectory($this->cachePath);
    }

    public function getMultiple($keys, $default = null)
    {
        $results = [];

        foreach ((array)$keys as $key) {
            $results[$key] = $this->get($key, $default);
        }

        return $results;
    }

    public function setMultiple($values, $ttl = null)
    {
        $success = true;

        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }

        return $success;
    }

    public function deleteMultiple($keys)
    {
        $success = true;

        foreach ((array)$keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }

        return $success;
    }

    public function has($key)
    {
        $filename = $this->getFilename($key);

        if (!file_exists($filename)) {
            return false;
        }

        $data = $this->readFile($filename);

        if ($data === null) {
            return false;
        }

        // Check expiration
        if ($data['expires'] > 0 && $data['expires'] < time()) {
            $this->delete($key);
            return false;
        }

        return true;
    }

    /**
     * Clear cache by namespace
     */
    public function clearNamespace(string $namespace): bool
    {
        $namespaceDir = $this->cachePath . '/' . $this->sanitizeNamespace($namespace);

        if (is_dir($namespaceDir)) {
            return $this->clearDirectory($namespaceDir);
        }

        return true;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        $stats = [
            'driver' => 'file',
            'path' => $this->cachePath,
            'total_files' => 0,
            'total_size' => 0,
            'namespaces' => [],
        ];

        $this->collectStats($this->cachePath, $stats);

        return $stats;
    }

    /**
     * Garbage collection - remove expired cache files
     */
    public function garbageCollection(): int
    {
        $cleaned = 0;
        $this->cleanDirectory($this->cachePath, $cleaned);
        return $cleaned;
    }

    private function getFilename(string $key): string
    {
        $safeKey = md5($key);

        if ($this->config['directory_level'] > 0) {
            $subDir = substr($safeKey, 0, 2);
            return $this->cachePath . '/' . $subDir . '/' . $safeKey . $this->config['extension'];
        }

        return $this->cachePath . '/' . $safeKey . $this->config['extension'];
    }

    private function readFile(string $filename): ?array
    {
        if (!file_exists($filename)) {
            return null;
        }

        $content = file_get_contents($filename, false, null, 0, 1000000); // Max 1MB

        if ($content === false) {
            return null;
        }

        try {
            $data = unserialize($content);

            if (!is_array($data) || !isset($data['value'], $data['expires'], $data['created'])) {
                // Invalid cache file, delete it
                unlink($filename);
                return null;
            }

            return $data;
        } catch (\Exception $e) {
            // Corrupted cache file, delete it
            unlink($filename);
            return null;
        }
    }

    private function writeFile(string $filename, array $data): bool
    {
        $content = serialize($data);
        $flags = LOCK_EX;

        if (!$this->config['file_locking']) {
            $flags = 0;
        }

        $result = file_put_contents($filename, $content, $flags);

        if ($result !== false) {
            // Set proper permissions
            chmod($filename, 0644);
            return true;
        }

        return false;
    }

    private function ensureCacheDirectory(): void
    {
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }

        // Ensure cache directory is writable
        if (!is_writable($this->cachePath)) {
            throw new \RuntimeException("Cache directory is not writable: {$this->cachePath}");
        }
    }

    private function clearDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return true;
        }

        $success = true;
        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                $success = $this->clearDirectory($path) && $success;
                rmdir($path);
            } else {
                $success = unlink($path) && $success;
            }
        }

        return $success;
    }

    private function cleanDirectory(string $dir, int &$cleaned): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                $this->cleanDirectory($path, $cleaned);

                // Remove empty directories
                if (count(scandir($path)) === 2) { // Only . and ..
                    rmdir($path);
                }
            } else {
                $data = $this->readFile($path);

                if ($data === null || ($data['expires'] > 0 && $data['expires'] < time())) {
                    unlink($path);
                    $cleaned++;
                }
            }
        }
    }

    private function collectStats(string $dir, array &$stats): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                $this->collectStats($path, $stats);
            } else {
                $stats['total_files']++;
                $stats['total_size'] += filesize($path);

                // Extract namespace from path
                $relativePath = str_replace($this->cachePath . '/', '', $path);
                $namespace = dirname($relativePath);

                if ($namespace !== '.') {
                    if (!isset($stats['namespaces'][$namespace])) {
                        $stats['namespaces'][$namespace] = ['files' => 0, 'size' => 0];
                    }
                    $stats['namespaces'][$namespace]['files']++;
                    $stats['namespaces'][$namespace]['size'] += filesize($path);
                }
            }
        }
    }

    private function sanitizeNamespace(string $namespace): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $namespace);
    }
}