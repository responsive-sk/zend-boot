<?php

declare(strict_types=1);

/**
 * Twig configuration for PSR-15 compliant application
 * 
 * This configuration is used by PathsAwareTwigEnvironmentFactory
 * to create properly configured Twig environment with centralized
 * path management.
 */

return [
    'twig' => [
        // Debug mode (should be false in production)
        'debug' => false,
        
        // Strict variables (throw exception on undefined variables)
        'strict_variables' => false,
        
        // Auto reload templates when they change
        'auto_reload' => false,
        
        // Global variables available in all templates
        'globals' => [
            // Add global variables here if needed
            // 'app_name' => 'Mezzio Light Application',
            // 'app_version' => '1.0.0',
        ],
        
        // Additional Twig options
        'options' => [
            // Charset for templates
            'charset' => 'UTF-8',
            
            // Optimizations
            'optimizations' => -1, // Enable all optimizations
        ],
    ],
];
