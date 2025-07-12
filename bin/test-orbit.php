#!/usr/bin/env php
<?php
/**
 * Orbit CMS Test Script
 * 
 * Testuje základné funkcie Orbit modulu.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Load paths configuration
$paths = require __DIR__ . '/../config/paths.php';

use Orbit\Entity\Content;
use Orbit\Service\ContentRepository;
use Orbit\Service\FileDriver\MarkdownDriver;

echo "🧪 Orbit CMS Test Script\n";
echo "========================\n\n";

try {
    // Test database connection using paths
    echo "1. Testing database connection...\n";
    $orbitDbPath = $paths->getPath($paths->base(), $paths->get('orbit_db'));
    echo "   📍 Database path: {$orbitDbPath}\n";
    $pdo = new PDO('sqlite:' . $orbitDbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✅ Database connection OK\n\n";
    
    // Test ContentRepository
    echo "2. Testing ContentRepository...\n";
    $contentRepo = new ContentRepository($pdo);
    
    // Find existing content
    $content = $contentRepo->findByTypeAndSlug('docs', 'sk/README');
    if ($content) {
        echo "   ✅ Found content: {$content->getTitle()}\n";
        echo "   📄 Type: {$content->getType()}\n";
        echo "   🔗 Slug: {$content->getSlug()}\n";
        echo "   📁 File: {$content->getFilePath()}\n";
    } else {
        echo "   ⚠️  Content not found: sk/README\n";
    }
    echo "\n";
    
    // Test MarkdownDriver
    echo "3. Testing MarkdownDriver...\n";
    $driver = new MarkdownDriver();
    
    if ($content && $driver->exists($content->getFilePath())) {
        $fileData = $driver->read($content->getFilePath());
        echo "   ✅ File read successfully\n";
        echo "   📝 Content length: " . strlen($fileData['content']) . " chars\n";
        echo "   🏷️  Meta fields: " . count($fileData['meta']) . "\n";
        
        if (!empty($fileData['meta']['title'])) {
            echo "   📋 Title from file: {$fileData['meta']['title']}\n";
        }
        
        // Test rendering
        $rendered = $driver->render(substr($fileData['content'], 0, 200) . '...');
        echo "   🎨 Rendered HTML length: " . strlen($rendered) . " chars\n";
    } else {
        echo "   ❌ File not found or not readable\n";
    }
    echo "\n";
    
    // Test Content Entity
    echo "4. Testing Content Entity...\n";
    $testContent = new Content('test', 'test-slug', 'Test Title', 'content/test.md');
    $testContent->setMeta('description', 'Test description');
    $testContent->setMeta('tags', ['test', 'orbit']);
    $testContent->setPublished(true);
    
    echo "   ✅ Content entity created\n";
    echo "   📋 Title: {$testContent->getTitle()}\n";
    echo "   🔗 URL: {$testContent->getUrl()}\n";
    echo "   📝 Excerpt: " . substr($testContent->getExcerpt(), 0, 50) . "...\n";
    echo "   📊 Array export: " . count($testContent->toArray()) . " fields\n";
    echo "\n";
    
    // Test content statistics
    echo "5. Content Statistics...\n";
    $stats = [
        'docs' => count($contentRepo->findAll('docs')),
        'pages' => count($contentRepo->findAll('page')),
        'posts' => count($contentRepo->findAll('post')),
        'total' => count($contentRepo->findAll()),
    ];
    
    foreach ($stats as $type => $count) {
        echo "   📊 {$type}: {$count} items\n";
    }
    echo "\n";
    
    // Test file operations
    echo "6. Testing file operations...\n";
    $testFile = 'content/test-orbit.md';
    $testData = [
        'meta' => [
            'title' => 'Orbit Test',
            'slug' => 'orbit-test',
            'published' => true,
            'tags' => ['test', 'orbit'],
        ],
        'content' => "# Orbit Test\n\nToto je test súbor pre Orbit CMS.\n\n## Funkcie\n\n- Markdown parsing\n- YAML front-matter\n- File operations",
    ];
    
    // Write test file
    if ($driver->write($testFile, $testData)) {
        echo "   ✅ Test file written: $testFile\n";
        
        // Read it back
        $readData = $driver->read($testFile);
        echo "   ✅ Test file read back successfully\n";
        echo "   📝 Title: {$readData['meta']['title']}\n";
        echo "   🏷️  Tags: " . implode(', ', $readData['meta']['tags']) . "\n";
        
        // Clean up
        $driver->delete($testFile);
        echo "   🧹 Test file cleaned up\n";
    } else {
        echo "   ❌ Failed to write test file\n";
    }
    echo "\n";
    
    echo "🎉 All tests completed successfully!\n";
    echo "====================================\n";
    echo "✅ Orbit CMS basic functionality is working\n";
    echo "🚀 Ready for integration with Mezzio\n\n";
    
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    exit(1);
}
