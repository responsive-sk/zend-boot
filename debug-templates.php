<?php

require_once 'vendor/autoload.php';

use App\Factory\TemplateConfigFactory;
use App\Template\PhpRendererFactory;
use Laminas\ServiceManager\ServiceManager;

// Create template config
$templateFactory = new TemplateConfigFactory();
$templateConfig = $templateFactory->getConfig();

echo "Template Config:\n";
print_r($templateConfig);

// Create mock container with config
$container = new ServiceManager([
    'services' => [
        'config' => $templateConfig
    ]
]);

// Create PhpRenderer
$rendererFactory = new PhpRendererFactory();
$renderer = $rendererFactory($container);

echo "\nPhpRenderer paths:\n";
print_r($renderer->getPaths());

// Test specific paths
echo "\nTesting specific paths:\n";
echo "User templates path: " . (new App\Service\UnifiedPathService(['paths' => ['root' => getcwd()]], 
    new League\Flysystem\Filesystem(new League\Flysystem\Local\LocalFilesystemAdapter('.')),
    new League\Flysystem\Filesystem(new League\Flysystem\Local\LocalFilesystemAdapter('.')),
    new League\Flysystem\Filesystem(new League\Flysystem\Local\LocalFilesystemAdapter('.'))
))->moduleTemplates('User', 'user') . "\n";

echo "Mark templates path: " . (new App\Service\UnifiedPathService(['paths' => ['root' => getcwd()]], 
    new League\Flysystem\Filesystem(new League\Flysystem\Local\LocalFilesystemAdapter('.')),
    new League\Flysystem\Filesystem(new League\Flysystem\Local\LocalFilesystemAdapter('.')),
    new League\Flysystem\Filesystem(new League\Flysystem\Local\LocalFilesystemAdapter('.'))
))->moduleTemplates('Mark', 'mark') . "\n";
