<?php

declare(strict_types=1);

/**
 * Build Configuration
 * 
 * Configure build settings for production deployment
 */

return [
    // Base URL for sitemap.xml and robots.txt
    'base_url' => 'https://dotkernel-light.sk',
    
    // Build settings
    'build' => [
        'directory' => './build',
        'package_name' => 'dotkernel-light',
        'version' => 'auto', // 'auto' = timestamp, or specify version like '1.0.0'
    ],
    
    // Web files configuration
    'web_files' => [
        'robots_txt' => [
            'enabled' => true,
            'crawl_delay' => 1,
            'additional_disallow' => [
                // Add custom paths to disallow
                // '/admin/',
                // '/api/internal/',
            ],
        ],
        
        'sitemap_xml' => [
            'enabled' => true,
            'additional_urls' => [
                // Add custom URLs to sitemap
                // [
                //     'loc' => '/contact',
                //     'changefreq' => 'monthly',
                //     'priority' => '0.7',
                // ],
            ],
        ],
        
        'htaccess_optimizations' => [
            'enabled' => true,
            'security_headers' => true,
            'cache_control' => true,
            'compression' => true,
            'browser_caching' => true,
        ],
    ],
    
    // Environment-specific overrides
    'environments' => [
        'production' => [
            'base_url' => 'https://dotkernel-light.sk',
        ],
        'staging' => [
            'base_url' => 'https://staging.dotkernel-light.sk',
        ],
        'development' => [
            'base_url' => 'http://localhost:8080',
        ],
    ],
];
