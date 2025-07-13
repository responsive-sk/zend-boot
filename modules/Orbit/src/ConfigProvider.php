<?php

declare(strict_types=1);

namespace Orbit;

use Orbit\Entity\Content;
use Orbit\Entity\Category;
use Orbit\Entity\Tag;
use Orbit\Service\OrbitManager;
use Orbit\Service\ContentRepository;
use Orbit\Service\CategoryRepository;
use Orbit\Service\TagRepository;
use Orbit\Service\SearchService;
use Orbit\Service\FileDriver\MarkdownDriver;
use Orbit\Service\FileDriver\JsonDriver;
use Orbit\Handler\DocsHandler;
use Orbit\Handler\PageHandler;
use Orbit\Handler\BlogHandler;
use Orbit\Handler\BlogTailwindHandler;
use Orbit\Handler\PostHandler;

use Orbit\Handler\ApiSearchHandler;
use Orbit\Handler\MarkContentHandler;
use Orbit\Handler\MarkDashboardHandler;
use Orbit\Handler\MarkEditorHandler;
use Orbit\Factory\OrbitManagerFactory;
use Orbit\Factory\ContentRepositoryFactory;
use Orbit\Factory\CategoryRepositoryFactory;
use Orbit\Factory\TagRepositoryFactory;
use Orbit\Factory\SearchServiceFactory;
use Orbit\Factory\MarkdownDriverFactory;
use Orbit\Factory\JsonDriverFactory;
use Orbit\Factory\DocsHandlerFactory;
use Orbit\Factory\PageHandlerFactory;
use Orbit\Factory\BlogHandlerFactory;
use Orbit\Factory\BlogTailwindHandlerFactory;
use Orbit\Factory\PostHandlerFactory;

use Orbit\Factory\ApiSearchHandlerFactory;
use Orbit\Factory\MarkContentHandlerFactory;
use Orbit\Factory\MarkDashboardHandlerFactory;
use Orbit\Factory\MarkEditorHandlerFactory;

/**
 * Orbit CMS Configuration Provider
 */
class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates' => $this->getTemplates(),
            'orbit' => $this->getOrbitConfig(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                // Core services
                OrbitManager::class => OrbitManagerFactory::class,
                ContentRepository::class => ContentRepositoryFactory::class,
                CategoryRepository::class => CategoryRepositoryFactory::class,
                TagRepository::class => TagRepositoryFactory::class,
                SearchService::class => SearchServiceFactory::class,
                
                // File drivers
                MarkdownDriver::class => MarkdownDriverFactory::class,
                JsonDriver::class => JsonDriverFactory::class,
                
                // Handlers
                DocsHandler::class => DocsHandlerFactory::class,
                PageHandler::class => PageHandlerFactory::class,
                BlogHandler::class => BlogHandlerFactory::class,
                BlogTailwindHandler::class => BlogTailwindHandlerFactory::class,
                PostHandler::class => PostHandlerFactory::class,

                ApiSearchHandler::class => ApiSearchHandlerFactory::class,
                MarkContentHandler::class => MarkContentHandlerFactory::class,
                MarkDashboardHandler::class => MarkDashboardHandlerFactory::class,
                MarkEditorHandler::class => MarkEditorHandlerFactory::class,
            ],
            'aliases' => [
                'orbit.manager' => OrbitManager::class,
                'orbit.content_repository' => ContentRepository::class,
                'orbit.search' => SearchService::class,
            ],
        ];
    }

    public function getTemplates(): array
    {
        return [
            'paths' => [
                'orbit' => [__DIR__ . '/../templates/orbit'],
            ],
        ];
    }

    public function getOrbitConfig(): array
    {
        return [
            // Content storage
            'content_path' => 'content',
            'media_path' => 'content/media',
            'templates_path' => 'content/templates',
            
            // Database
            'database' => [
                'dsn' => 'sqlite:data/orbit.db',
                'options' => [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ],
            ],
            
            // File drivers
            'drivers' => [
                'markdown' => MarkdownDriver::class,
                'json' => JsonDriver::class,
            ],
            'default_driver' => 'markdown',
            
            // Content types
            'content_types' => [
                'page' => [
                    'name' => 'Stránky',
                    'path' => 'pages',
                    'driver' => 'markdown',
                    'template' => 'orbit::page/view',
                ],
                'post' => [
                    'name' => 'Blog články',
                    'path' => 'posts',
                    'driver' => 'markdown',
                    'template' => 'orbit::post/view',
                ],
                'docs' => [
                    'name' => 'Dokumentácia',
                    'path' => 'docs',
                    'driver' => 'markdown',
                    'template' => 'orbit::docs/view',
                ],
            ],
            
            // Search configuration
            'search' => [
                'enabled' => true,
                'min_query_length' => 3,
                'max_results' => 50,
                'highlight_tags' => ['<mark>', '</mark>'],
            ],
            
            // Cache configuration
            'cache' => [
                'enabled' => true,
                'ttl' => 3600, // 1 hour
                'prefix' => 'orbit:',
            ],
            
            // Mark integration
            'mark' => [
                'enabled' => true,
                'base_path' => '/mark/orbit',
                'permissions' => [
                    'read' => 'mark',
                    'write' => 'mark',
                    'delete' => 'mark',
                    'media' => 'mark',
                ],
            ],
            
            // Public routes
            'routes' => [
                'docs_enabled' => true,
                'api_enabled' => true,
                'base_paths' => [
                    'docs' => '/docs',
                    'pages' => '/page',
                    'posts' => '/blog',
                    'api' => '/api/orbit',
                ],
            ],
        ];
    }
}
