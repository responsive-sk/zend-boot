#!/usr/bin/env php
<?php
/**
 * Template Renderer Test
 * 
 * Testuje template renderer funkcionalitu.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Laminas\ServiceManager\ServiceManager;

echo "🎨 Template Renderer Test\n";
echo "=========================\n\n";

try {
    // Test 1: Container setup
    echo "1. Testing container setup...\n";
    $container = require __DIR__ . '/../config/container.php';
    
    if ($container instanceof ServiceManager) {
        echo "   ✅ Container created successfully\n";
    } else {
        throw new Exception("Container not properly configured");
    }
    echo "\n";
    
    // Test 2: Template renderer
    echo "2. Testing template renderer...\n";
    
    try {
        $templateRenderer = $container->get('Mezzio\Template\TemplateRendererInterface');
        echo "   ✅ TemplateRenderer service available\n";
        echo "   📋 Type: " . get_class($templateRenderer) . "\n";
    } catch (Exception $e) {
        echo "   ❌ TemplateRenderer not available: " . $e->getMessage() . "\n";
        throw $e;
    }
    echo "\n";
    
    // Test 3: Template paths
    echo "3. Testing template paths...\n";
    
    $templatePaths = [
        'templates/layout/home.phtml',
        'modules/Mark/templates/mark/login.phtml',
        'modules/Orbit/templates/orbit/docs/view.phtml',
    ];
    
    foreach ($templatePaths as $path) {
        if (file_exists($path)) {
            echo "   ✅ Template exists: $path\n";
        } else {
            echo "   ❌ Template missing: $path\n";
        }
    }
    echo "\n";
    
    // Test 4: Simple template rendering
    echo "4. Testing template rendering...\n";
    
    try {
        // Try to render a simple template
        $html = $templateRenderer->render('error::404', [
            'title' => 'Test Page'
        ]);
        
        if (!empty($html)) {
            echo "   ✅ Template rendering works\n";
            echo "   📄 Rendered HTML length: " . strlen($html) . " bytes\n";
        } else {
            echo "   ❌ Template rendering returned empty result\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Template rendering failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Test 5: Mark services
    echo "5. Testing Mark services...\n";
    
    $markServices = [
        'Mark\Service\MarkUserRepository',
        'Mark\Handler\LoginHandler',
    ];
    
    foreach ($markServices as $service) {
        try {
            $serviceInstance = $container->get($service);
            echo "   ✅ $service available\n";
        } catch (Exception $e) {
            echo "   ❌ $service failed: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
    
    echo "🎉 Template renderer tests completed!\n";
    echo "====================================\n";
    
} catch (Exception $e) {
    echo "❌ Template renderer test failed: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    exit(1);
}
