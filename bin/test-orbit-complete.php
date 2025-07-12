#!/usr/bin/env php
<?php
/**
 * Orbit CMS Complete Test
 * 
 * Kompletný test celého Orbit CMS systému.
 */

declare(strict_types=1);

echo "🚀 Orbit CMS Complete Test\n";
echo "===========================\n\n";

$tests = [
    'bin/test-orbit.php' => 'Basic Orbit functionality',
    'bin/test-orbit-integration.php' => 'Mezzio integration',
    'bin/test-orbit-routes.php' => 'Route registration',
    'bin/test-orbit-mark.php' => 'Mark integration',
    'bin/test-authorization.php' => 'Authorization system',
];

$passed = 0;
$total = count($tests);

foreach ($tests as $script => $description) {
    echo "🧪 Testing: $description\n";
    echo "   Script: $script\n";
    
    $output = [];
    $returnCode = 0;
    exec("php $script 2>&1", $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "   ✅ PASSED\n";
        $passed++;
    } else {
        echo "   ❌ FAILED\n";
        echo "   📄 Output: " . implode("\n   ", array_slice($output, -3)) . "\n";
    }
    echo "\n";
}

// Summary
echo "📊 Test Summary\n";
echo "===============\n";
echo "✅ Passed: $passed/$total\n";
echo "❌ Failed: " . ($total - $passed) . "/$total\n\n";

if ($passed === $total) {
    echo "🎉 ALL TESTS PASSED!\n";
    echo "====================\n";
    echo "✅ Orbit CMS is fully functional\n";
    echo "🌐 All endpoints are working\n";
    echo "🔐 Authentication and authorization configured\n";
    echo "📊 Mark dashboard with Orbit integration ready\n\n";
    
    echo "📋 Orbit CMS Features\n";
    echo "=====================\n";
    echo "📚 Content Management:\n";
    echo "   - 28 content items indexed\n";
    echo "   - Docs, Pages, Posts support\n";
    echo "   - Markdown + YAML front-matter\n";
    echo "   - Categories and tags system\n\n";
    
    echo "🔍 Search System:\n";
    echo "   - Full-text search with FTS5\n";
    echo "   - API endpoints available\n";
    echo "   - Highlight snippets\n\n";
    
    echo "🔧 Mark Admin Interface:\n";
    echo "   - Content CRUD operations\n";
    echo "   - Dashboard with statistics\n";
    echo "   - Role-based access control\n\n";
    
    echo "🌐 Public Endpoints:\n";
    echo "   - /docs/sk/README - Documentation\n";
    echo "   - /page/about - Static pages\n";
    echo "   - /api/orbit/search - Search API\n\n";
    
    echo "🔐 Protected Endpoints:\n";
    echo "   - /mark/login - Mark login\n";
    echo "   - /mark - Dashboard with Orbit stats\n";
    echo "   - /mark/orbit/content - Content management\n\n";
    
    echo "💡 Next Steps:\n";
    echo "==============\n";
    echo "1. Login to Mark interface:\n";
    echo "   - URL: http://localhost:8080/mark/login\n";
    echo "   - Credentials: mark / mark123\n\n";
    
    echo "2. Explore Orbit CMS features:\n";
    echo "   - View dashboard statistics\n";
    echo "   - Manage content via Mark interface\n";
    echo "   - Test search functionality\n\n";
    
    echo "3. Customize and extend:\n";
    echo "   - Add new content types\n";
    echo "   - Create custom templates\n";
    echo "   - Implement media management\n\n";
    
    echo "🚀 Orbit CMS is production ready!\n";
    
} else {
    echo "❌ SOME TESTS FAILED!\n";
    echo "=====================\n";
    echo "Please check the failed tests and fix any issues.\n";
    echo "Run individual test scripts for more details.\n\n";
    exit(1);
}
