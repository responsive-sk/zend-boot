<?php

declare(strict_types=1);

namespace App\Service;

use Psr\SimpleCache\CacheInterface;

/**
 * HDM Boot Protocol - Unified Cache Service
 *
 * Multi-driver cache service with namespace support
 */
class CacheService implements CacheInterface
{
    private array $config;
    private UnifiedPathService $paths;
    private array $drivers = [];
    private string $namespace = 'global';

    public function __construct(array $config, UnifiedPathService $paths)
    {
        $this->config = $config;
        $this->paths = $paths;
    }

    public function get($key, $default = null)
    {
        $driver = $this->getDriver();
        $namespacedKey = $this->getNamespacedKey($key);

        return $driver->get($namespacedKey, $default);
    }

    public function set($key, $value, $ttl = null)
    {
        $driver = $this->getDriver();
        $namespacedKey = $this->getNamespacedKey($key);
        $effectiveTtl = $ttl ?? $this->config['default_ttl'] ?? 3600;

        return $driver->set($namespacedKey, $value, $effectiveTtl);
    }

    public function delete($key)
    {
        $driver = $this->getDriver();
        $namespacedKey = $this->getNamespacedKey($key);

        return $driver->delete($namespacedKey);
    }

    public function clear()
    {
        $driver = $this->getDriver();
        return $driver->clear();
    }

    public function getMultiple($keys, $default = null)
    {
        $driver = $this->getDriver();
        $namespacedKeys = array_map([$this, 'getNamespacedKey'], (array)$keys);

        return $driver->getMultiple($namespacedKeys, $default);
    }

    public function setMultiple($values, $ttl = null)
    {
        $driver = $this->getDriver();
        $namespacedValues = [];

        foreach ($values as $key => $value) {
            $namespacedValues[$this->getNamespacedKey($key)] = $value;
        }

        return $driver->setMultiple($namespacedValues, $ttl);
    }

    public function deleteMultiple($keys)
    {
        $driver = $this->getDriver();
        $namespacedKeys = array_map([$this, 'getNamespacedKey'], (array)$keys);

        return $driver->deleteMultiple($namespacedKeys);
    }

    public function has($key)
    {
        $driver = $this->getDriver();
        $namespacedKey = $this->getNamespacedKey($key);

        return $driver->has($namespacedKey);
    }

    /**
     * Cache with namespace for modules
     */
    public function withNamespace(string $namespace): self
    {
        $service = clone $this;
        $service->namespace = $namespace;
        return $service;
    }

    /**
     * Clear cache by namespace pattern
     */
    public function clearNamespace(string $namespace): bool
    {
        $driver = $this->getDriver();

        if ($driver instanceof \App\Service\FileCacheDriver) {
            return $driver->clearNamespace($namespace);
        }

        // Fallback for other drivers
        return $this->clear();
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        $driver = $this->getDriver();

        if (method_exists($driver, 'getStats')) {
            return $driver->getStats();
        }

        return [
            'driver' => $this->config['driver'] ?? 'unknown',
            'namespace' => $this->namespace,
            'items' => 'unknown',
            'size' => 'unknown',
        ];
    }

    private function getDriver(): CacheInterface
    {
        $driverType = $this->config['driver'] ?? 'file';

        if (!isset($this->drivers[$driverType])) {
            $this->drivers[$driverType] = $this->createDriver($driverType);
        }

        return $this->drivers[$driverType];
    }

    private function createDriver(string $type): CacheInterface
    {
        switch ($type) {
            case 'file':
                return new \App\Service\FileCacheDriver(
                    $this->paths->cache(),
                    $this->config['drivers']['file'] ?? []
                );

            case 'apc':
            case 'apcu':
                if (extension_loaded('apcu')) {
                    return new \Symfony\Component\Cache\Psr16Cache(
                        new \Symfony\Component\Cache\Adapter\ApcuAdapter(
                            $this->config['drivers']['apc']['prefix'] ?? 'hdm_'
                        )
                    );
                }
                // Fallback to file cache if APC not available
                return $this->createDriver('file');

            default:
                throw new \InvalidArgumentException("Unsupported cache driver: {$type}");
        }
    }

    private function getNamespacedKey(string $key): string
    {
        return "{$this->namespace}.{$key}";
    }
}