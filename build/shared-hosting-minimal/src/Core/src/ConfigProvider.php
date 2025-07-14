<?php

declare(strict_types=1);

namespace Light\Core;

use Light\Core\Factory\PathsAwareTwigEnvironmentFactory;
use Light\Core\Factory\TemplatePathProviderFactory;
use Light\Core\Service\TemplatePathProviderInterface;
use Twig\Environment;

/**
 * Core module configuration provider
 * 
 * Provides core infrastructure services following Zend4Boot protocol
 * and PSR-15 compliance. This module contains shared services used
 * across the entire application.
 */
class ConfigProvider
{
    /**
     * Return configuration for this module
     * 
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }
    
    /**
     * Return dependency configuration
     * 
     * @return array<string, mixed>
     */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                // Template path provider
                TemplatePathProviderInterface::class => TemplatePathProviderFactory::class,
                
                // Paths-aware Twig environment (overrides default)
                Environment::class => PathsAwareTwigEnvironmentFactory::class,
            ],
        ];
    }
}
