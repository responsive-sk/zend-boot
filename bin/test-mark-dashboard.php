#!/usr/bin/env php
<?php
/**
 * Mark Dashboard Test
 * 
 * Testuje Mark dashboard s Orbit integráciou.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Laminas\ServiceManager\ServiceManager;

echo "📊 Mark Dashboard Test\n";
echo "======================\n\n";

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
    
    // Test 2: OrbitManager availability
    echo "2. Testing OrbitManager availability...\n";
    
    try {
        $orbitManager = $container->get('Orbit\Service\OrbitManager');
        echo "   ✅ OrbitManager available\n";
        echo "   📋 Type: " . get_class($orbitManager) . "\n";
    } catch (Exception $e) {
        echo "   ❌ OrbitManager not available: " . $e->getMessage() . "\n";
        throw $e;
    }
    echo "\n";
    
    // Test 3: DashboardHandler creation
    echo "3. Testing DashboardHandler creation...\n";
    
    try {
        $dashboardHandler = $container->get('Mark\Handler\DashboardHandler');
        echo "   ✅ DashboardHandler created successfully\n";
        echo "   📋 Type: " . get_class($dashboardHandler) . "\n";
    } catch (Exception $e) {
        echo "   ❌ DashboardHandler creation failed: " . $e->getMessage() . "\n";
        echo "   📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        throw $e;
    }
    echo "\n";
    
    // Test 4: Mark services
    echo "4. Testing Mark services...\n";
    
    $markServices = [
        'Mark\Service\MarkUserRepository',
        'Mark\Service\SystemStatsService',
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
    
    // Test 5: Template renderer
    echo "5. Testing template renderer...\n";
    
    try {
        $templateRenderer = $container->get('Mezzio\Template\TemplateRendererInterface');
        echo "   ✅ TemplateRenderer available\n";
        
        // Test Mark dashboard template
        if (file_exists('modules/Mark/templates/mark/dashboard.phtml')) {
            echo "   ✅ Dashboard template exists\n";
        } else {
            echo "   ❌ Dashboard template missing\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ TemplateRenderer failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    echo "🎉 Mark dashboard tests completed!\n";
    echo "=================================\n";
    
} catch (Exception $e) {
    echo "❌ Mark dashboard test failed: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    exit(1);
}
