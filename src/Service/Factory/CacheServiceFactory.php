<?php

declare(strict_types=1);

namespace App\Service\Factory;

use Psr\Container\ContainerInterface;
use App\Service\CacheService;
use App\Service\UnifiedPathService;

class CacheServiceFactory
{
    public function __invoke(ContainerInterface $container): CacheService
    {
        $config = $container->get('config');
        $paths = $container->get(UnifiedPathService::class);

        $cacheConfig = $config['cache'] ?? [
            'default_ttl' => 3600,
            'prefix' => 'hdm_',
            'driver' => 'file',
            'drivers' => [
                'file' => [
                    'path' => $paths->cache(),
                    'extension' => '.cache',
                ]
            ]
        ];

        return new CacheService($cacheConfig, $paths);
    }
}