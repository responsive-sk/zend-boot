<?php

declare(strict_types=1);

namespace Orbit\Factory;

use Orbit\Service\ContentRepository;
use Psr\Container\ContainerInterface;
use ResponsiveSk\Slim4Paths\Paths;
use PDO;

/**
 * Content Repository Factory
 */
class ContentRepositoryFactory
{
    public function __invoke(ContainerInterface $container): ContentRepository
    {
        // Get Paths service for secure path resolution
        $paths = $container->get(Paths::class);
        assert($paths instanceof Paths);

        $config = $container->get('config');
        assert(is_array($config));

        $orbitConfig = $config['orbit'] ?? [];
        assert(is_array($orbitConfig));

        $dbConfig = $orbitConfig['database'] ?? [];
        assert(is_array($dbConfig));

        // Use Paths service to resolve database path
        $orbitDbPath = $paths->getPath($paths->base(), $paths->get('orbit_db'));
        $dsn = 'sqlite:' . $orbitDbPath;

        $username = $dbConfig['username'] ?? null;
        assert(is_string($username) || $username === null);

        $password = $dbConfig['password'] ?? null;
        assert(is_string($password) || $password === null);

        $options = $dbConfig['options'] ?? [];
        assert(is_array($options));

        // Ensure database directory exists
        $dbDir = dirname($orbitDbPath);
        if (!is_dir($dbDir)) {
            if (!mkdir($dbDir, 0755, true) && !is_dir($dbDir)) {
                throw new \RuntimeException("Failed to create database directory: {$dbDir}");
            }
        }

        $pdo = new PDO($dsn, $username, $password, $options);

        return new ContentRepository($pdo);
    }
}
