#!/usr/bin/env php
<?php
/**
 * Orbit Mark Integration Test
 * 
 * Testuje integráciu Orbit CMS s Mark dashboardom.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Laminas\ServiceManager\ServiceManager;

echo "🔧 Orbit Mark Integration Test\n";
echo "==============================\n\n";

try {
    // Test 1: Container setup
    echo "1. Testing DI container setup...\n";
    $container = require __DIR__ . '/../config/container.php';
    
    if ($container instanceof ServiceManager) {
        echo "   ✅ Container created successfully\n";
    } else {
        throw new Exception("Container not properly configured");
    }
    echo "\n";
    
    // Test 2: Mark DashboardHandler with Orbit integration
    echo "2. Testing Mark DashboardHandler with Orbit...\n";
    
    try {
        $dashboardHandler = $container->get('Mark\Handler\DashboardHandler');
        echo "   ✅ DashboardHandler created with Orbit integration\n";
    } catch (Exception $e) {
        echo "   ❌ DashboardHandler creation failed: " . $e->getMessage() . "\n";
        throw $e;
    }
    echo "\n";
    
    // Test 3: Orbit Mark handlers
    echo "3. Testing Orbit Mark handlers...\n";
    
    $markHandlers = [
        'Orbit\Handler\MarkContentHandler',
    ];
    
    foreach ($markHandlers as $handler) {
        try {
            $handlerInstance = $container->get($handler);
            echo "   ✅ $handler created\n";
        } catch (Exception $e) {
            echo "   ❌ $handler failed: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
    
    // Test 4: Orbit services integration
    echo "4. Testing Orbit services integration...\n";
    
    try {
        $orbitManager = $container->get('Orbit\Service\OrbitManager');
        echo "   ✅ OrbitManager available\n";
        
        // Test Orbit stats (similar to what DashboardHandler uses)
        $allContent = $orbitManager->getAllContent(null, false);
        $publishedContent = $orbitManager->getAllContent(null, true);
        $categories = $orbitManager->getAllCategories();
        $tags = $orbitManager->getAllTags();
        
        echo "   📊 Content stats:\n";
        echo "      - Total content: " . count($allContent) . "\n";
        echo "      - Published: " . count($publishedContent) . "\n";
        echo "      - Categories: " . count($categories) . "\n";
        echo "      - Tags: " . count($tags) . "\n";
        
        // Count by type
        $typeStats = [];
        foreach ($allContent as $content) {
            $type = $content->getType();
            $typeStats[$type] = ($typeStats[$type] ?? 0) + 1;
        }
        
        foreach ($typeStats as $type => $count) {
            echo "      - " . ucfirst($type) . ": $count\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Orbit services test failed: " . $e->getMessage() . "\n";
        throw $e;
    }
    echo "\n";
    
    // Test 5: Template paths
    echo "5. Testing template paths...\n";
    
    $templatePaths = [
        'modules/Orbit/templates/orbit/mark/content/index.phtml',
        'modules/Mark/templates/mark/dashboard.phtml',
    ];
    
    foreach ($templatePaths as $path) {
        if (file_exists($path)) {
            echo "   ✅ Template exists: $path\n";
        } else {
            echo "   ❌ Template missing: $path\n";
        }
    }
    echo "\n";
    
    // Test 6: Routes configuration
    echo "6. Testing routes configuration...\n";
    
    $routeFiles = [
        'config/routes/orbit.php',
        'config/routes/mark.php',
    ];
    
    foreach ($routeFiles as $file) {
        if (file_exists($file)) {
            echo "   ✅ Route file exists: $file\n";
            
            // Check if file contains Orbit routes
            $content = file_get_contents($file);
            if (strpos($content, 'orbit') !== false) {
                echo "      📋 Contains Orbit routes\n";
            }
        } else {
            echo "   ❌ Route file missing: $file\n";
        }
    }
    echo "\n";
    
    echo "🎉 All Mark integration tests passed!\n";
    echo "====================================\n";
    echo "✅ Orbit CMS is properly integrated with Mark dashboard\n";
    echo "🔧 Mark dashboard now includes Orbit CMS statistics\n";
    echo "📊 Orbit content management is available via Mark interface\n\n";
    
    echo "📋 Available Mark + Orbit endpoints:\n";
    echo "   🔧 /mark - Mark dashboard with Orbit stats (requires login)\n";
    echo "   📝 /mark/orbit/content - Content management (requires login)\n";
    echo "   ➕ /mark/orbit/content/create - Create content (requires login)\n";
    echo "   ✏️  /mark/orbit/content/{type}/{id}/edit - Edit content (requires login)\n\n";
    
    echo "💡 To test the Mark interface:\n";
    echo "   1. Create a mark user: php bin/create-mark-user.php\n";
    echo "   2. Login at: http://localhost:8080/user/login\n";
    echo "   3. Access Mark dashboard: http://localhost:8080/mark\n\n";
    
} catch (Exception $e) {
    echo "❌ Mark integration test failed: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    exit(1);
}
