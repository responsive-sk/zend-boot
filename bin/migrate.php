#!/usr/bin/env php
<?php

declare(strict_types=1);

// Change to the project root
chdir(dirname(__DIR__));

require 'vendor/autoload.php';

// Build container
$container = require 'config/container.php';

try {
    echo "Starting database migration...\n";
    
    // Get migration service
    $migrationService = $container->get(\App\Database\MigrationService::class);
    
    // Run migrations
    $migrationService->migrate();
    
    echo "✅ Database migration completed successfully!\n";
    echo "📁 Databases created:\n";
    echo "   - data/user.db (users table)\n";
    echo "   - data/mark.db (marks table)\n";
    echo "\n👤 Default users created:\n";
    echo "   - admin/admin123 (admin, user roles)\n";
    echo "   - user/user123 (user role)\n";
    echo "   - mark/mark123 (mark, user roles)\n";
    echo "\n🚀 You can now start the application!\n";
    
} catch (\Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
