<?php

declare(strict_types=1);

namespace Light\App\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ResponsiveSk\Slim4Paths\Paths;

/**
 * Example handler demonstrating Paths usage
 */
class PathsExampleHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly Paths $paths
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Demonstrate various path methods
        $pathsInfo = [
            'base_path'      => $this->paths->base(),
            'config_path'    => $this->paths->config(),
            'src_path'       => $this->paths->src(),
            'public_path'    => $this->paths->public(),
            'templates_path' => $this->paths->templates(),
            'cache_path'     => $this->paths->cache(),
            'logs_path'      => $this->paths->logs(),
            'uploads_path'   => $this->paths->uploads(),

            // Specific file examples
            'config_file' => $this->paths->config('config.php'),
            'log_file'    => $this->paths->logs('app.log'),
            'upload_file' => $this->paths->uploads('example.jpg'),

            // Asset paths
            'css_path'    => $this->paths->css('app.css'),
            'js_path'     => $this->paths->js('main.js'),
            'images_path' => $this->paths->images('logo.png'),

            // All configured paths
            'all_paths' => $this->paths->all(),

            // Available presets
            'available_presets' => Paths::getAvailablePresets(),
            'preset_info'       => Paths::getPresetInfo(),
        ];

        return new JsonResponse([
            'message' => 'Paths service example',
            'paths'   => $pathsInfo,
        ]);
    }
}
