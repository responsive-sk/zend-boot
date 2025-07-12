<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ResponsiveSk\Slim4Paths\Paths;

return [
    'dependencies' => [
        'factories' => [
            // Modern Paths Service with MezzioOrbit preset
            Paths::class => \App\Service\Factory\PathsServiceFactory::class,

            // HDM Boot Protocol - Unified Path Service (PILLAR III)
            \App\Service\PathServiceInterface::class => \App\Service\UnifiedPathServiceFactory::class,

            // Legacy aliases for backward compatibility - use factory

            \Mezzio\Template\TemplateRendererInterface::class => \Mezzio\LaminasView\LaminasViewRendererFactory::class,

            // Database services
            'pdo.user' => \App\Database\PdoFactory::class,
            'pdo.mark' => \App\Database\PdoFactory::class,
            'pdo.system' => \App\Database\PdoFactory::class,
            \App\Database\MigrationService::class => \App\Database\MigrationServiceFactory::class,


        ],
    ],
];
