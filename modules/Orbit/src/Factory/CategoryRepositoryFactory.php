<?php

declare(strict_types=1);

namespace Orbit\Factory;

use Orbit\Service\CategoryRepository;
use Psr\Container\ContainerInterface;
use PDO;

/**
 * Category Repository Factory
 */
class CategoryRepositoryFactory
{
    public function __invoke(ContainerInterface $container): CategoryRepository
    {
        $config = $container->get('config');
        assert(is_array($config));

        $orbitConfig = $config['orbit'] ?? [];
        assert(is_array($orbitConfig));

        $dbConfig = $orbitConfig['database'] ?? [];
        assert(is_array($dbConfig));

        $dsn = $dbConfig['dsn'] ?? '';
        assert(is_string($dsn));

        $username = $dbConfig['username'] ?? null;
        assert(is_string($username) || $username === null);

        $password = $dbConfig['password'] ?? null;
        assert(is_string($password) || $password === null);

        $options = $dbConfig['options'] ?? [];
        assert(is_array($options));

        $pdo = new PDO($dsn, $username, $password, $options);

        return new CategoryRepository($pdo);
    }
}
