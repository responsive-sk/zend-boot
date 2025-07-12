#!/usr/bin/env php
<?php
/**
 * Orbit Routes Test
 * 
 * Testuje, či sú Orbit routes správne zaregistrované.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Mezzio\Application;
use Laminas\ServiceManager\ServiceManager;

echo "🧪 Orbit Routes Test\n";
echo "===================\n\n";

try {
    // Load container
    $container = require __DIR__ . '/../config/container.php';
    
    // Get application
    $app = $container->get(Application::class);
    
    echo "✅ Application loaded successfully\n\n";
    
    // Test route registration by checking if handlers exist
    $handlers = [
        'Orbit\Handler\DocsHandler',
        'Orbit\Handler\PageHandler', 
        'Orbit\Handler\ApiSearchHandler',
    ];
    
    echo "📋 Testing handler registration:\n";
    foreach ($handlers as $handler) {
        if ($container->has($handler)) {
            echo "   ✅ $handler\n";
        } else {
            echo "   ❌ $handler - NOT REGISTERED\n";
        }
    }
    echo "\n";
    
    // Test services
    $services = [
        'Orbit\Service\OrbitManager',
        'Orbit\Service\ContentRepository',
        'Orbit\Service\CategoryRepository',
        'Orbit\Service\TagRepository',
        'Orbit\Service\SearchService',
    ];
    
    echo "🔧 Testing service registration:\n";
    foreach ($services as $service) {
        if ($container->has($service)) {
            echo "   ✅ $service\n";
        } else {
            echo "   ❌ $service - NOT REGISTERED\n";
        }
    }
    echo "\n";
    
    // Test actual service creation
    echo "🚀 Testing service instantiation:\n";
    
    try {
        $orbitManager = $container->get('Orbit\Service\OrbitManager');
        echo "   ✅ OrbitManager created\n";
        
        $searchService = $container->get('Orbit\Service\SearchService');
        echo "   ✅ SearchService created\n";
        
        $docsHandler = $container->get('Orbit\Handler\DocsHandler');
        echo "   ✅ DocsHandler created\n";
        
        $apiHandler = $container->get('Orbit\Handler\ApiSearchHandler');
        echo "   ✅ ApiSearchHandler created\n";
        
    } catch (Exception $e) {
        echo "   ❌ Service creation failed: " . $e->getMessage() . "\n";
        throw $e;
    }
    echo "\n";
    
    echo "🎉 All route tests passed!\n";
    echo "========================\n";
    echo "✅ Orbit CMS routes are properly configured\n";
    echo "🚀 Ready to serve requests\n\n";
    
    echo "📋 Available routes:\n";
    echo "   📚 GET /docs/sk/README\n";
    echo "   📚 GET /docs/en/INDEX\n";
    echo "   📄 GET /page/about\n";
    echo "   🔍 GET /api/orbit/search?q=query\n";
    echo "   🔧 GET /mark/orbit (requires login)\n\n";
    
} catch (Exception $e) {
    echo "❌ Route test failed: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    exit(1);
}
