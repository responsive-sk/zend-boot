<?php

declare(strict_types=1);

namespace App\Service\Preset;

use ResponsiveSk\Slim4Paths\Presets\AbstractPreset;

/**
 * Custom Hybrid Preset for Mezzio + Orbit CMS + HDM Boot Protocol
 * 
 * Combines Mezzio framework structure with:
 * - Orbit CMS content management
 * - HDM Boot Protocol directory organization
 * - Theme system support
 * - Legacy compatibility paths
 */
class MezzioOrbitPreset extends AbstractPreset
{
    public function getName(): string
    {
        return 'Mezzio + Orbit CMS';
    }

    public function getDescription(): string
    {
        return 'Custom hybrid preset combining Mezzio framework with Orbit CMS, theme system, and HDM Boot Protocol directory structure';
    }

    /**
     * Get hybrid directory structure
     * 
     * @return array<string, string>
     */
    public function getPaths(): array
    {
        return array_merge($this->getCommonPaths(), [
            // === MEZZIO CORE ===
            'src' => $this->buildPath('src'),
            'config' => $this->buildPath('config'),
            'templates' => $this->buildPath('templates'),
            'modules' => $this->buildPath('modules'),
            'bin' => $this->buildPath('bin'),

            // === MEZZIO SOURCE STRUCTURE ===
            'handlers' => $this->buildPath('src/Handler'),
            'middleware' => $this->buildPath('src/Middleware'),
            'services' => $this->buildPath('src/Service'),
            'factories' => $this->buildPath('src/Factory'),

            // === MEZZIO CONFIG ===
            'autoload' => $this->buildPath('config/autoload'),
            'routes' => $this->buildPath('config/routes'),

            // === MEZZIO TEMPLATES ===
            'layouts' => $this->buildPath('templates/layout'),
            'app_templates' => $this->buildPath('templates/app'),
            'error_templates' => $this->buildPath('templates/error'),

            // === HDM BOOT PROTOCOL - VAR STRUCTURE ===
            'var' => $this->buildPath('var'),
            'storage' => $this->buildPath('var/storage'),
            'logs' => $this->buildPath('var/logs'),
            'cache' => $this->buildPath('var/cache'),
            'sessions' => $this->buildPath('var/sessions'),

            // === ORBIT CMS STRUCTURE ===
            'content' => $this->buildPath('content'),
            'pages' => $this->buildPath('content/pages'),
            'posts' => $this->buildPath('content/posts'),
            'docs' => $this->buildPath('content/docs'),
            'docs_sk' => $this->buildPath('content/docs/sk'),
            'docs_en' => $this->buildPath('content/docs/en'),

            // === ORBIT CMS MODULES ===
            'orbit_module' => $this->buildPath('modules/Orbit'),
            'orbit_src' => $this->buildPath('modules/Orbit/src'),
            'orbit_templates' => $this->buildPath('modules/Orbit/templates'),
            'mark_module' => $this->buildPath('modules/Mark'),
            'user_module' => $this->buildPath('modules/User'),

            // === THEME SYSTEM ===
            'themes' => $this->buildPath('themes'),
            'theme_bootstrap' => $this->buildPath('themes/bootstrap'),
            'theme_tailwind' => $this->buildPath('themes/tailwind'),

            // === PUBLIC ASSETS ===
            'assets' => $this->buildPath('public/assets'),
            'css' => $this->buildPath('public/assets/css'),
            'js' => $this->buildPath('public/assets/js'),
            'images' => $this->buildPath('public/assets/images'),
            'fonts' => $this->buildPath('public/assets/fonts'),
            'uploads' => $this->buildPath('public/uploads'),

            // === DATABASE PATHS ===
            'database' => $this->buildPath('var/storage'),
            'orbit_db' => $this->buildPath('var/storage/orbit.db'),
            'mark_db' => $this->buildPath('var/storage/mark.db'),
            'user_db' => $this->buildPath('var/storage/user.db'),

            // === LEGACY COMPATIBILITY ===
            'data' => $this->buildPath('data'),
            'legacy_cache' => $this->buildPath('data/cache'),
            'legacy_uploads' => $this->buildPath('data/uploads'),
            'legacy_db' => $this->buildPath('data'),

            // === PACKAGES ===
            'packages' => $this->buildPath('packages'),
            'slim4_paths' => $this->buildPath('packages/slim4-paths'),

            // === DEVELOPMENT ===
            'examples' => $this->buildPath('examples'),
            'scripts' => $this->buildPath('scripts'),
        ]);
    }

    /**
     * Get hybrid-specific helper methods
     * 
     * @return array<string, string>
     */
    public function getHelperMethods(): array
    {
        return array_merge(parent::getHelperMethods(), [
            // Mezzio methods
            'src' => 'Get source directory path',
            'handlers' => 'Get handlers directory path',
            'middleware' => 'Get middleware directory path',
            'services' => 'Get services directory path',
            'modules' => 'Get modules directory path',
            'autoload' => 'Get autoload config directory path',
            
            // HDM Boot Protocol methods
            'var' => 'Get var directory path (HDM Boot Protocol)',
            'storage' => 'Get storage directory path (databases)',
            'logs' => 'Get logs directory path',
            'cache' => 'Get cache directory path',
            'sessions' => 'Get sessions directory path',
            
            // Orbit CMS methods
            'content' => 'Get content directory path',
            'pages' => 'Get pages directory path',
            'posts' => 'Get posts directory path',
            'docs' => 'Get docs directory path',
            'docs_sk' => 'Get Slovak docs directory path',
            'docs_en' => 'Get English docs directory path',
            'orbit_module' => 'Get Orbit module directory path',
            'mark_module' => 'Get Mark module directory path',
            
            // Theme system methods
            'themes' => 'Get themes directory path',
            'theme_bootstrap' => 'Get Bootstrap theme directory path',
            'theme_tailwind' => 'Get Tailwind theme directory path',
            
            // Database methods
            'database' => 'Get database directory path',
            'orbit_db' => 'Get Orbit database file path',
            'mark_db' => 'Get Mark database file path',
            'user_db' => 'Get User database file path',
            
            // Asset methods
            'uploads' => 'Get uploads directory path',
            'assets' => 'Get assets directory path',
            
            // Package methods
            'packages' => 'Get packages directory path',
            'slim4_paths' => 'Get slim4-paths package directory path',
        ]);
    }
}
