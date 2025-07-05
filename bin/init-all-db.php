#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Master Database Initialization Script
 * 
 * HDM Boot Protocol Compliant - All System Databases
 * Initializes all three databases: user.db, mark.db, system.db
 */

echo "🚀 HDM Boot Protocol - Complete Database Initialization\n";
echo "=======================================================\n\n";

echo "📋 Initializing all HDM Boot Protocol databases...\n";
echo "   1. User Database (user.db)\n";
echo "   2. Mark Database (mark.db)\n";
echo "   3. System Database (system.db)\n\n";

$errors = [];
$basePath = __DIR__;

// Initialize User Database
echo "🔄 Step 1/3: Initializing User Database...\n";
echo str_repeat("-", 50) . "\n";
$output = [];
$returnCode = 0;
exec("php {$basePath}/init-user-db.php 2>&1", $output, $returnCode);

if ($returnCode === 0) {
    echo implode("\n", $output) . "\n";
    echo "✅ User database initialization completed\n\n";
} else {
    $errors[] = "User database initialization failed";
    echo "❌ User database initialization failed:\n";
    echo implode("\n", $output) . "\n\n";
}

// Initialize Mark Database
echo "🔄 Step 2/3: Initializing Mark Database...\n";
echo str_repeat("-", 50) . "\n";
$output = [];
$returnCode = 0;
exec("php {$basePath}/init-mark-db.php 2>&1", $output, $returnCode);

if ($returnCode === 0) {
    echo implode("\n", $output) . "\n";
    echo "✅ Mark database initialization completed\n\n";
} else {
    $errors[] = "Mark database initialization failed";
    echo "❌ Mark database initialization failed:\n";
    echo implode("\n", $output) . "\n\n";
}

// Initialize System Database
echo "🔄 Step 3/3: Initializing System Database...\n";
echo str_repeat("-", 50) . "\n";
$output = [];
$returnCode = 0;
exec("php {$basePath}/init-system-db.php 2>&1", $output, $returnCode);

if ($returnCode === 0) {
    echo implode("\n", $output) . "\n";
    echo "✅ System database initialization completed\n\n";
} else {
    $errors[] = "System database initialization failed";
    echo "❌ System database initialization failed:\n";
    echo implode("\n", $output) . "\n\n";
}

// Final summary
echo str_repeat("=", 60) . "\n";
if (empty($errors)) {
    echo "🎉 HDM Boot Protocol Database Initialization SUCCESSFUL!\n\n";
    
    echo "📊 All databases initialized:\n";
    echo "   ✅ user.db   - User management system\n";
    echo "   ✅ mark.db   - Mark management system\n";
    echo "   ✅ system.db - Core system services\n\n";
    
    echo "🔐 Default credentials:\n";
    echo "   👤 admin/admin123 (admin, user roles)\n";
    echo "   👤 user/user123 (user role)\n";
    echo "   👤 mark/mark123 (mark, user roles)\n\n";
    
    echo "🎯 Your application is now HDM Boot Protocol compliant!\n";
    echo "🚀 Ready for production deployment.\n";
    
    exit(0);
} else {
    echo "❌ HDM Boot Protocol Database Initialization FAILED!\n\n";
    echo "💥 Errors encountered:\n";
    foreach ($errors as $error) {
        echo "   - {$error}\n";
    }
    echo "\n🔧 Please check the error messages above and try again.\n";
    
    exit(1);
}
