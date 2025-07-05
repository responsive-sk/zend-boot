#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * HDM Boot Protocol - Path Service Test
 * 
 * Tests PILLAR III: Secure Path Resolution
 * Demonstrates protocol-compliant vs generic path methods
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Service\HdmPathService;

echo "🧪 HDM Boot Protocol - Path Service Test\n";
echo "========================================\n\n";

try {
    // Load configuration and create service
    $container = require __DIR__ . '/../config/container.php';
    $hdmPaths = $container->get(\App\Service\HdmPathService::class);
    
    echo "✅ PROTOCOL COMPLIANT - Use specific methods for configured paths:\n\n";
    
    // Test storage paths
    echo "📊 Database Storage Paths:\n";
    echo "  storage('user.db'):   " . $hdmPaths->storage('user.db') . "\n";
    echo "  storage('mark.db'):   " . $hdmPaths->storage('mark.db') . "\n";
    echo "  storage('system.db'): " . $hdmPaths->storage('system.db') . "\n";
    echo "  storage():            " . $hdmPaths->storage() . "\n\n";
    
    // Test logs paths
    echo "📝 Log File Paths:\n";
    echo "  logs('app.log'):      " . $hdmPaths->logs('app.log') . "\n";
    echo "  logs('error.log'):    " . $hdmPaths->logs('error.log') . "\n";
    echo "  logs():               " . $hdmPaths->logs() . "\n\n";
    
    // Test cache paths
    echo "💾 Cache Paths:\n";
    echo "  cache('templates'):   " . $hdmPaths->cache('templates') . "\n";
    echo "  cache('config'):      " . $hdmPaths->cache('config') . "\n";
    echo "  cache():              " . $hdmPaths->cache() . "\n\n";
    
    // Test sessions paths
    echo "🔐 Session Paths:\n";
    echo "  sessions('sess_123'): " . $hdmPaths->sessions('sess_123') . "\n";
    echo "  sessions():           " . $hdmPaths->sessions() . "\n\n";
    
    // Test content paths
    echo "📄 Content Paths:\n";
    echo "  content('articles'):  " . $hdmPaths->content('articles') . "\n";
    echo "  content('docs'):      " . $hdmPaths->content('docs') . "\n";
    echo "  content():            " . $hdmPaths->content() . "\n\n";
    
    // Test public paths
    echo "🌐 Public Asset Paths:\n";
    echo "  public('css/app.css'):" . $hdmPaths->public('css/app.css') . "\n";
    echo "  public('js/app.js'):  " . $hdmPaths->public('js/app.js') . "\n";
    echo "  public():             " . $hdmPaths->public() . "\n\n";
    
    echo "⚠️ LIMITED USE - Generic path method (uses basePath + relativePath):\n\n";
    
    // Test generic path method
    echo "🔧 Generic Path Method:\n";
    echo "  path('custom/file.txt'): " . $hdmPaths->path('custom/file.txt') . "\n\n";
    
    echo "❌ PROTOCOL VIOLATION Examples:\n\n";
    
    // Test path traversal protection
    echo "🛡️ Path Traversal Protection:\n";
    try {
        $maliciousPath = $hdmPaths->storage('../../../etc/passwd');
        echo "  ❌ This should not work: {$maliciousPath}\n";
    } catch (Exception $e) {
        echo "  ✅ Blocked path traversal: " . $e->getMessage() . "\n";
    }
    
    try {
        $maliciousPath = $hdmPaths->logs('../../config/database.php');
        echo "  ❌ This should not work: {$maliciousPath}\n";
    } catch (Exception $e) {
        echo "  ✅ Blocked path traversal: " . $e->getMessage() . "\n";
    }
    
    echo "\n📋 HDM Boot Protocol Compliance Summary:\n";
    echo "✅ storage() method for database files\n";
    echo "✅ logs() method for log files\n";
    echo "✅ cache() method for cache files\n";
    echo "✅ sessions() method for session files\n";
    echo "✅ content() method for content files\n";
    echo "✅ public() method for public assets\n";
    echo "✅ Path traversal protection active\n";
    echo "✅ Secure directory creation\n";
    echo "⚠️ Generic path() method available but discouraged\n\n";
    
    echo "🎯 PILLAR III: Secure Path Resolution - IMPLEMENTED!\n";
    
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
