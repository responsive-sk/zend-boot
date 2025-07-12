<?php

declare(strict_types=1);

/**
 * HDM Boot Protocol - Paths Configuration
 * 
 * Custom MezzioOrbit preset for our hybrid project structure
 */

use ResponsiveSk\Slim4Paths\Paths;

// Get project root
$rootPath = dirname(__DIR__);

// Define custom paths array
$customPaths = [
    // === CORE PATHS ===
    'root' => '',
    'public' => 'public',
    'config' => 'config',
    'src' => 'src',
    'vendor' => 'vendor',
    'bin' => 'bin',

    // === MEZZIO STRUCTURE ===
    'templates' => 'templates',
    'modules' => 'modules',
    'handlers' => 'src/Handler',
    'middleware' => 'src/Middleware',
    'services' => 'src/Service',
    'factories' => 'src/Factory',

    // === MEZZIO CONFIG ===
    'autoload' => 'config/autoload',
    'routes' => 'config/routes',

    // === MEZZIO TEMPLATES ===
    'layouts' => 'templates/layout',
    'app_templates' => 'templates/app',
    'error_templates' => 'templates/error',

    // === HDM BOOT PROTOCOL - VAR STRUCTURE ===
    'var' => 'var',
    'storage' => 'var/storage',
    'logs' => 'var/logs',
    'cache' => 'var/cache',
    'sessions' => 'var/sessions',
    'uploads' => 'var/uploads',

    // === ORBIT CMS STRUCTURE ===
    'content' => 'content',
    'pages' => 'content/pages',
    'posts' => 'content/posts',
    'docs' => 'content/docs',
    'docs_sk' => 'content/docs/sk',
    'docs_en' => 'content/docs/en',

    // === ORBIT CMS MODULES ===
    'orbit_module' => 'modules/Orbit',
    'orbit_src' => 'modules/Orbit/src',
    'orbit_templates' => 'modules/Orbit/templates',
    'mark_module' => 'modules/Mark',
    'mark_src' => 'modules/Mark/src',
    'mark_templates' => 'modules/Mark/templates',
    'user_module' => 'modules/User',
    'user_src' => 'modules/User/src',
    'user_templates' => 'modules/User/templates',

    // === THEME SYSTEM ===
    'themes' => 'templates/themes',
    'theme_bootstrap' => 'templates/themes/bootstrap',
    'theme_tailwind' => 'templates/themes/tailwind',

    // === PUBLIC ASSETS ===
    'assets' => 'public/assets',
    'css' => 'public/assets/css',
    'js' => 'public/assets/js',
    'images' => 'public/assets/images',
    'fonts' => 'public/assets/fonts',
    'public_uploads' => 'public/uploads',

    // === DATABASE PATHS ===
    'database' => 'var/storage',
    'orbit_db' => 'var/storage/orbit.db',
    'mark_db' => 'var/storage/mark.db',
    'user_db' => 'var/storage/user.db',
    'system_db' => 'var/storage/system.db',

    // === LEGACY COMPATIBILITY (DEPRECATED - USE VAR/) ===
    'legacy_data' => 'data',
    'legacy_cache' => 'data/cache',
    'legacy_uploads' => 'data/uploads',
    'legacy_db' => 'data',

    // === PACKAGES ===
    'packages' => 'packages',
    'slim4_paths' => 'packages/slim4-paths',

    // === DEVELOPMENT ===
    'examples' => 'examples',
    'scripts' => 'scripts',
];

// Create and return Paths instance with custom paths
return new Paths($rootPath, $customPaths);
