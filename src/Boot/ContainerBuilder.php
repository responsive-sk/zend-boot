<?php

declare(strict_types=1);

namespace App\Boot;

use Psr\Container\ContainerInterface;

/**
 * HDM Boot Protocol - Container Builder
 * 
 * Centralized container building and configuration
 */
class ContainerBuilder
{
    private string $configPath;

    public function __construct(string $configPath = 'config/container.php')
    {
        $this->configPath = $configPath;
    }

    /**
     * Build and configure the DI container
     */
    public function build(): ContainerInterface
    {
        if (!file_exists($this->configPath)) {
            throw new \RuntimeException("Container config not found: {$this->configPath}");
        }

        $container = require $this->configPath;
        
        if (!$container instanceof ContainerInterface) {
            throw new \RuntimeException('Container config must return ContainerInterface instance');
        }

        return $container;
    }

    /**
     * Build container with custom config path
     */
    public static function buildWithConfig(string $configPath): ContainerInterface
    {
        $builder = new self($configPath);
        return $builder->build();
    }

    /**
     * Build container with default config
     */
    public static function buildDefault(): ContainerInterface
    {
        $builder = new self();
        return $builder->build();
    }
}
