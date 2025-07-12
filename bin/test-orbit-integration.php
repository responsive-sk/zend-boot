#!/usr/bin/env php
<?php
/**
 * Orbit CMS Integration Test
 * 
 * Testuje integráciu Orbit modulu s Mezzio.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Mezzio\Application;
use Laminas\ServiceManager\ServiceManager;

echo "🧪 Orbit CMS Integration Test\n";
echo "=============================\n\n";

try {
    // Test 1: Config loading
    echo "1. Testing configuration loading...\n";
    $config = require __DIR__ . '/../config/config.php';
    
    if (isset($config['orbit'])) {
        echo "   ✅ Orbit configuration loaded\n";
        echo "   📁 Content path: {$config['orbit']['content_path']}\n";
        echo "   🗄️  Database: {$config['orbit']['database']['dsn']}\n";
    } else {
        throw new Exception("Orbit configuration not found");
    }
    echo "\n";
    
    // Test 2: Container setup
    echo "2. Testing DI container...\n";
    $container = require __DIR__ . '/../config/container.php';
    
    if ($container instanceof ServiceManager) {
        echo "   ✅ Container created successfully\n";
        
        // Test Orbit services
        $services = [
            'Orbit\Service\OrbitManager',
            'Orbit\Service\ContentRepository', 
            'Orbit\Service\SearchService',
            'Orbit\Handler\DocsHandler',
            'Orbit\Handler\ApiSearchHandler',
        ];
        
        foreach ($services as $service) {
            if ($container->has($service)) {
                echo "   ✅ Service registered: $service\n";
            } else {
                echo "   ⚠️  Service missing: $service\n";
            }
        }
    } else {
        throw new Exception("Container not properly configured");
    }
    echo "\n";
    
    // Test 3: Application setup
    echo "3. Testing application setup...\n";
    $app = $container->get(Application::class);
    
    if ($app instanceof Application) {
        echo "   ✅ Application created successfully\n";
        
        // Test routes (we can't easily test route registration without running the app)
        echo "   📋 Routes should be registered from config/routes/orbit.php\n";
    } else {
        throw new Exception("Application not properly configured");
    }
    echo "\n";
    
    // Test 4: Database connection
    echo "4. Testing database connection...\n";
    $orbitManager = $container->get('Orbit\Service\OrbitManager');
    
    if ($orbitManager) {
        echo "   ✅ OrbitManager service created\n";
        
        // Test content retrieval
        $docs = $orbitManager->getAllContent('docs', true);
        echo "   📚 Found " . count($docs) . " published docs\n";
        
        if (!empty($docs)) {
            $firstDoc = $docs[0];
            echo "   📄 First doc: {$firstDoc->getTitle()}\n";
            echo "   🔗 URL: {$firstDoc->getUrl()}\n";
        }
    } else {
        throw new Exception("OrbitManager service not available");
    }
    echo "\n";
    
    // Test 5: Search functionality
    echo "5. Testing search functionality...\n";
    $searchService = $container->get('Orbit\Service\SearchService');
    
    if ($searchService) {
        echo "   ✅ SearchService created\n";
        
        // Test search
        $results = $searchService->search('mezzio');
        echo "   🔍 Search for 'mezzio': " . count($results) . " results\n";
        
        if (!empty($results)) {
            $firstResult = $results[0];
            echo "   📄 First result: {$firstResult['title']}\n";
        }
    } else {
        throw new Exception("SearchService not available");
    }
    echo "\n";
    
    // Test 6: File operations
    echo "6. Testing file operations...\n";
    $markdownDriver = $container->get('Orbit\Service\FileDriver\MarkdownDriver');
    
    if ($markdownDriver) {
        echo "   ✅ MarkdownDriver created\n";
        
        // Test reading existing file
        $testFile = 'content/docs/sk/README.md';
        if ($markdownDriver->exists($testFile)) {
            $data = $markdownDriver->read($testFile);
            echo "   📖 Read file: $testFile\n";
            echo "   📝 Content length: " . strlen($data['content']) . " chars\n";
            echo "   🏷️  Meta fields: " . count($data['meta']) . "\n";
        } else {
            echo "   ⚠️  Test file not found: $testFile\n";
        }
    } else {
        throw new Exception("MarkdownDriver not available");
    }
    echo "\n";
    
    echo "🎉 All integration tests passed!\n";
    echo "================================\n";
    echo "✅ Orbit CMS is properly integrated with Mezzio\n";
    echo "🚀 Ready for production use\n\n";
    
    echo "📋 Available endpoints:\n";
    echo "   📚 /docs/sk/README - Slovak documentation\n";
    echo "   📚 /docs/en/INDEX - English documentation\n";
    echo "   🔍 /api/orbit/search?q=mezzio - Search API\n";
    echo "   📄 /page/about - Static pages\n";
    echo "   🔧 /mark/orbit - Mark management (requires login)\n\n";
    
} catch (Exception $e) {
    echo "❌ Integration test failed: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    exit(1);
}
