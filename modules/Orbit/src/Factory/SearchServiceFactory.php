<?php

declare(strict_types=1);

namespace Orbit\Factory;

use Orbit\Service\SearchService;
use Psr\Container\ContainerInterface;
use PDO;

/**
 * Search Service Factory
 */
class SearchServiceFactory
{
    public function __invoke(ContainerInterface $container): SearchService
    {
        $config = $container->get('config');
        assert(is_array($config));

        $orbitConfig = $config['orbit'] ?? [];
        assert(is_array($orbitConfig));

        $searchConfig = $orbitConfig['search'] ?? [];
        assert(is_array($searchConfig));

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

        return new SearchService($pdo, $searchConfig);
    }
}
