<?php

declare(strict_types=1);

/**
 * Shared Hosting Configuration
 * 
 * Optimized configuration for shared hosting environments
 */

return [
    // === SHARED HOSTING OPTIMIZATIONS ===
    'shared_hosting' => [
        'enabled' => true,
        'cache_enabled' => true,
        'debug_mode' => false,
        'error_reporting' => false,
        'memory_limit' => '128M',
        'max_execution_time' => 30,
    ],

    // === PERFORMANCE OPTIMIZATIONS ===
    'performance' => [
        'opcache_enabled' => true,
        'session_cache_limiter' => 'nocache',
        'gzip_compression' => true,
        'static_cache_headers' => true,
    ],

    // === SECURITY SETTINGS ===
    'security' => [
        'hide_php_version' => true,
        'disable_server_signature' => true,
        'secure_headers' => true,
        'csrf_protection' => true,
    ],

    // === DATABASE SETTINGS ===
    'database' => [
        'type' => 'sqlite',
        'connection_pooling' => false,
        'persistent_connections' => false,
        'timeout' => 10,
    ],

    // === LOGGING SETTINGS ===
    'logging' => [
        'level' => 'error',
        'file_rotation' => true,
        'max_file_size' => '10MB',
        'max_files' => 5,
    ],

    // === CACHE SETTINGS ===
    'cache' => [
        'driver' => 'file',
        'ttl' => 3600,
        'prefix' => 'hdm_',
        'cleanup_probability' => 100,
    ],
];
