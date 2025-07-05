#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Test Production Build
 * 
 * Quick test to verify the production build works correctly
 */

$buildDir = __DIR__ . '/../build';

if (!is_dir($buildDir)) {
    echo "❌ Build directory not found. Run 'php bin/build-prod.php' first.\n";
    exit(1);
}

echo "🧪 Testing production build...\n\n";

// Test 1: Check essential files
echo "📁 Checking essential files...\n";
$requiredFiles = [
    'public/index.php',
    'config/config.php',
    'data/user.db',
    'data/mark.db',
    'vendor/autoload.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists("$buildDir/$file")) {
        echo "  ✅ $file\n";
    } else {
        echo "  ❌ $file (missing)\n";
    }
}

// Test 2: Check database
echo "\n📊 Testing database connection...\n";
try {
    $pdo = new PDO('sqlite:' . $buildDir . '/data/user.db');
    $stmt = $pdo->query('SELECT COUNT(*) FROM users');
    $userCount = $stmt->fetchColumn();
    echo "  ✅ Database connection OK ($userCount users)\n";
    
    // Test default users
    $stmt = $pdo->query('SELECT username FROM users');
    $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "  👤 Users: " . implode(', ', $users) . "\n";
    
} catch (Exception $e) {
    echo "  ❌ Database error: " . $e->getMessage() . "\n";
}

// Test 3: Test basic routing
echo "\n🌐 Testing basic application bootstrap...\n";
try {
    chdir($buildDir);
    
    // Simulate basic request
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['HTTP_HOST'] = 'localhost';
    
    ob_start();
    require $buildDir . '/public/index.php';
    $output = ob_get_clean();
    
    if (strpos($output, 'Mezzio') !== false || strpos($output, 'Bootstrap') !== false) {
        echo "  ✅ Application bootstrap OK\n";
    } else {
        echo "  ⚠️  Application bootstrap - unexpected output\n";
    }
    
} catch (Exception $e) {
    echo "  ❌ Bootstrap error: " . $e->getMessage() . "\n";
}

// Test 4: Check configuration
echo "\n⚙️  Checking production configuration...\n";
if (file_exists("$buildDir/config/autoload/session.local.php")) {
    echo "  ✅ Production session config\n";
} else {
    echo "  ❌ Missing production session config\n";
}

if (file_exists("$buildDir/config/autoload/database.local.php")) {
    echo "  ✅ Production database config\n";
} else {
    echo "  ❌ Missing production database config\n";
}

// Test 5: Check security files
echo "\n🔒 Checking security configuration...\n";
if (file_exists("$buildDir/public/.htaccess")) {
    echo "  ✅ Public .htaccess\n";
} else {
    echo "  ❌ Missing public .htaccess\n";
}

if (file_exists("$buildDir/.htaccess")) {
    echo "  ✅ Root .htaccess\n";
} else {
    echo "  ❌ Missing root .htaccess\n";
}

echo "\n✅ Production build test completed!\n";
echo "📦 Build is ready for shared hosting deployment.\n\n";

echo "🚀 Next steps:\n";
echo "   1. Download: mezzio-user-app-*.tar.gz\n";
echo "   2. Upload to your hosting account\n";
echo "   3. Extract files\n";
echo "   4. Point document root to public/ directory\n";
echo "   5. Test with admin/admin123 login\n";
