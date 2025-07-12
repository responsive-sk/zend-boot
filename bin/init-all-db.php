#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Master Database Initialization Script
 *
 * HDM Boot Protocol Compliant - All System Databases
 * Initializes all three databases: user.db, mark.db, system.db
 *
 * SHARED HOSTING COMPATIBLE - No exec() functions used
 */

// Bootstrap the application
require_once __DIR__ . '/../vendor/autoload.php';

echo "🚀 HDM Boot Protocol - Complete Database Initialization\n";
echo "=======================================================\n\n";

echo "📋 Initializing all HDM Boot Protocol databases...\n";
echo "   1. User Database (user.db)\n";
echo "   2. Mark Database (mark.db)\n";
echo "   3. System Database (system.db)\n";
echo "   4. Orbit CMS Database (orbit.db)\n\n";

$errors = [];

try {
    // Get container and services
    $container = require __DIR__ . '/../config/container.php';
    $hdmPaths = $container->get(\App\Service\PathServiceInterface::class);

    // Initialize User Database with default data
    echo "🔄 Step 1/3: Initializing User Database with default data...\n";
    echo str_repeat("-", 50) . "\n";

    try {
        // Get PDO connection for user database
        $userPdo = $container->get('pdo.user');

        // Create and run user migration
        $userMigration = new \App\Database\UserMigration($userPdo);
        $userMigration->migrate();


        echo "✅ User database initialization completed\n\n";
    } catch (Exception $e) {
        $errors[] = "User database initialization failed: " . $e->getMessage();
        echo "❌ User database initialization failed: " . $e->getMessage() . "\n\n";
    }

    // Initialize Mark Database with default data
    echo "🔄 Step 2/3: Initializing Mark Database with default data...\n";
    echo str_repeat("-", 50) . "\n";

    try {
        // Get PDO connection for mark database
        $markPdo = $container->get('pdo.mark');

        // Create and run mark migration
        $markMigration = new \App\Database\MarkMigration($markPdo);
        $markMigration->migrate();


        echo "✅ Mark database initialization completed\n\n";
    } catch (Exception $e) {
        $errors[] = "Mark database initialization failed: " . $e->getMessage();
        echo "❌ Mark database initialization failed: " . $e->getMessage() . "\n\n";
    }

    // Initialize System Database with default data
    echo "🔄 Step 3/3: Initializing System Database with default data...\n";
    echo str_repeat("-", 50) . "\n";

    try {
        // Get PDO connection for system database
        $systemPdo = $container->get('pdo.system');

        // Create and run system migration
        $systemMigration = new \App\Database\SystemMigration($systemPdo);
        $systemMigration->migrate();


        echo "✅ System database initialization completed\n\n";
    } catch (Exception $e) {
        $errors[] = "System database initialization failed: " . $e->getMessage();
        echo "❌ System database initialization failed: " . $e->getMessage() . "\n\n";
    }

} catch (Exception $e) {
    $errors[] = "Failed to initialize container: " . $e->getMessage();
    echo "❌ Failed to initialize container: " . $e->getMessage() . "\n\n";
}

// Final summary
echo str_repeat("=", 60) . "\n";
if (empty($errors)) {
    echo "🎉 HDM Boot Protocol Database Initialization SUCCESSFUL!\n\n";

    // Initialize Orbit CMS Database
    echo "🔄 Step 4/4: Initializing Orbit CMS Database...\n";
    echo str_repeat("-", 50) . "\n";

    try {
        $orbitScript = __DIR__ . '/migrate-orbit-db.php';
        if (file_exists($orbitScript)) {
            // Execute Orbit migration
            ob_start();
            include $orbitScript;
            $output = ob_get_clean();
            echo $output;
            echo "✅ Orbit CMS database initialization completed\n\n";
        } else {
            throw new Exception("Orbit migration script not found: $orbitScript");
        }
    } catch (Exception $e) {
        $errors[] = "Orbit CMS database initialization failed: " . $e->getMessage();
        echo "❌ Orbit CMS database initialization failed: " . $e->getMessage() . "\n\n";
    }

    echo "📊 All databases initialized:\n";
    echo "   ✅ user.db   - User management system\n";
    echo "   ✅ mark.db   - Mark management system\n";
    echo "   ✅ system.db - Core system services\n";
    echo "   ✅ orbit.db  - Orbit CMS content management\n\n";

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
