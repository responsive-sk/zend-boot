<?php

declare(strict_types=1);

namespace Light\App\Factory;

use Psr\Container\ContainerInterface;
use ResponsiveSk\Slim4Paths\Paths;

use function assert;

/**
 * Factory for template paths configuration using Paths service
 */
class TemplatePathsFactory
{
    /**
     * @return array{
     *     paths: array{
     *          app: array{string},
     *          error: array{string},
     *          layout: array{string},
     *          partial: array{string},
     *     }
     * }
     */
    public function __invoke(ContainerInterface $container): array
    {
        $paths = $container->get(Paths::class);
        assert($paths instanceof Paths);

        return [
            'paths' => [
                'app'     => [$paths->src('App/templates/app')],
                'error'   => [$paths->src('App/templates/error')],
                'layout'  => [$paths->src('App/templates/layout')],
                'partial' => [$paths->src('App/templates/partial')],
            ],
        ];
    }
}
