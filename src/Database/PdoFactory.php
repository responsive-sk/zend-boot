<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use Psr\Container\ContainerInterface;

class PdoFactory
{
    public function __invoke(ContainerInterface $container, string $requestedName): PDO
    {
        $config = $container->get('config');
        assert(is_array($config));

        if (!isset($config['database']) || !is_array($config['database'])) {
            throw new \InvalidArgumentException('Database configuration not found');
        }

        $dbConfig = $config['database'];

        // Extract database name from service name (e.g., 'pdo.user' -> 'user')
        $dbName = str_replace('pdo.', '', $requestedName);

        if (!isset($dbConfig[$dbName]) || !is_array($dbConfig[$dbName])) {
            throw new \InvalidArgumentException("Database configuration for '{$dbName}' not found");
        }

        $dbSettings = $dbConfig[$dbName];

        if (isset($dbSettings['driver']) && $dbSettings['driver'] === 'sqlite') {
            $dsn = 'sqlite:' . $dbSettings['database'];

            // Ensure directory exists
            $dir = dirname($dbSettings['database']);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $pdo = new PDO($dsn);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Enable foreign keys for SQLite
            $pdo->exec('PRAGMA foreign_keys = ON');

            return $pdo;
        }

        $driver = $dbSettings['driver'] ?? 'unknown';
        throw new \InvalidArgumentException("Unsupported database driver: {$driver}");
    }
}
