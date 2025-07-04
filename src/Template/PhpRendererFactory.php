<?php

declare(strict_types=1);

namespace App\Template;

use Psr\Container\ContainerInterface;

class PhpRendererFactory
{
    public function __invoke(ContainerInterface $container): PhpRenderer
    {
        $config = $container->get('config');
        $templateConfig = $config['templates'] ?? [];
        
        $renderer = new PhpRenderer($templateConfig);
        
        // Add template paths
        if (isset($templateConfig['paths'])) {
            foreach ($templateConfig['paths'] as $namespace => $paths) {
                foreach ((array) $paths as $path) {
                    $renderer->addPath($path, $namespace);
                }
            }
        }
        
        return $renderer;
    }
}
