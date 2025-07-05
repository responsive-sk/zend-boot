#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * HDM Boot Protocol - User Database Cleanup
 * 
 * Removes mark users from user.db (they should be in mark.db only)
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🧹 HDM Boot Protocol - User Database Cleanup\n";
echo "============================================\n\n";

try {
    $container = require __DIR__ . '/../config/container.php';
    $userPdo = $container->get('pdo.user');
    
    echo "📊 Current users in user.db:\n";
    $stmt = $userPdo->query('SELECT id, username, roles FROM users');
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        $roles = json_decode($user['roles'], true) ?: [];
        echo "  - {$user['username']}: " . implode(', ', $roles) . "\n";
    }
    
    echo "\n🔍 Identifying mark users to remove...\n";
    
    $markRoles = ['mark', 'editor', 'supermark', 'admin'];
    $usersToRemove = [];
    
    foreach ($users as $user) {
        $roles = json_decode($user['roles'], true) ?: [];
        
        // Check if user has any mark roles
        $hasMarkRole = false;
        foreach ($markRoles as $markRole) {
            if (in_array($markRole, $roles, true)) {
                $hasMarkRole = true;
                break;
            }
        }
        
        // Also check for admin role (legacy)
        if (in_array('admin', $roles, true)) {
            $hasMarkRole = true;
        }
        
        if ($hasMarkRole) {
            $usersToRemove[] = $user;
            echo "  ❌ Will remove: {$user['username']} (mark user)\n";
        } else {
            echo "  ✅ Will keep: {$user['username']} (regular user)\n";
        }
    }
    
    if (empty($usersToRemove)) {
        echo "\n✅ No mark users found in user.db - database is clean!\n";
        exit(0);
    }
    
    echo "\n⚠️ About to remove " . count($usersToRemove) . " mark users from user.db\n";
    echo "These users should exist in mark.db instead.\n\n";
    
    // Confirm removal
    echo "Continue? (y/N): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) !== 'y') {
        echo "❌ Cleanup cancelled.\n";
        exit(1);
    }
    
    echo "\n🗑️ Removing mark users from user.db...\n";
    
    $stmt = $userPdo->prepare('DELETE FROM users WHERE id = ?');
    
    foreach ($usersToRemove as $user) {
        $stmt->execute([$user['id']]);
        echo "  🗑️ Removed: {$user['username']}\n";
    }
    
    echo "\n📊 Final users in user.db:\n";
    $stmt = $userPdo->query('SELECT username, roles FROM users');
    $remainingUsers = $stmt->fetchAll();
    
    if (empty($remainingUsers)) {
        echo "  (No users remaining - only regular users should be here)\n";
    } else {
        foreach ($remainingUsers as $user) {
            $roles = json_decode($user['roles'], true) ?: [];
            echo "  - {$user['username']}: " . implode(', ', $roles) . "\n";
        }
    }
    
    echo "\n✅ User database cleanup completed!\n";
    echo "📋 HDM Boot Protocol Database Separation:\n";
    echo "  - user.db: Regular users only\n";
    echo "  - mark.db: Mark users only (mark, editor, supermark)\n";
    echo "  - system.db: Core system data\n";
    
} catch (Exception $e) {
    echo "❌ Cleanup failed: " . $e->getMessage() . "\n";
    exit(1);
}
