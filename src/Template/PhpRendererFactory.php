<?php

declare(strict_types=1);

namespace App\Template;

use Psr\Container\ContainerInterface;

class PhpRendererFactory
{
    public function __invoke(ContainerInterface $container): PhpRenderer
    {
        $config = $container->get('config');
        assert(is_array($config));

        $templateConfig = $config['templates'] ?? [];
        assert(is_array($templateConfig));

        $renderer = new PhpRenderer($templateConfig);

        // Add template paths
        if (isset($templateConfig['paths']) && is_array($templateConfig['paths'])) {
            foreach ($templateConfig['paths'] as $namespace => $paths) {
                if (is_array($paths)) {
                    foreach ($paths as $path) {
                        if (is_string($path)) {
                            $renderer->addPath($path, $namespace);
                        }
                    }
                }
            }
        }

        return $renderer;
    }
}
