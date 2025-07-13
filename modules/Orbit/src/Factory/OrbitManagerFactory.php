<?php

declare(strict_types=1);

namespace Orbit\Factory;

use Orbit\Service\OrbitManager;
use Orbit\Service\ContentRepository;
use Orbit\Service\CategoryRepository;
use Orbit\Service\TagRepository;
use Orbit\Service\SearchService;
use Orbit\Service\FileDriver\MarkdownDriver;
use Orbit\Service\FileDriver\JsonDriver;
use Psr\Container\ContainerInterface;

/**
 * Orbit Manager Factory
 */
class OrbitManagerFactory
{
    public function __invoke(ContainerInterface $container): OrbitManager
    {
        $config = $container->get('config');
        assert(is_array($config));

        $orbitConfig = $config['orbit'] ?? [];
        assert(is_array($orbitConfig));

        $contentRepository = $container->get(ContentRepository::class);
        assert($contentRepository instanceof ContentRepository);

        $categoryRepository = $container->get(CategoryRepository::class);
        assert($categoryRepository instanceof CategoryRepository);

        $tagRepository = $container->get(TagRepository::class);
        assert($tagRepository instanceof TagRepository);

        $searchService = $container->get(SearchService::class);
        assert($searchService instanceof SearchService);

        // Initialize file drivers
        $drivers = [];
        $driverClasses = $orbitConfig['drivers'] ?? [];
        assert(is_array($driverClasses));

        foreach ($driverClasses as $driverName => $driverClass) {
            assert(is_string($driverName));
            assert(is_string($driverClass));
            $drivers[$driverName] = $container->get($driverClass);
        }

        return new OrbitManager(
            $orbitConfig,
            $contentRepository,
            $categoryRepository,
            $tagRepository,
            $searchService,
            $drivers
        );
    }
}
