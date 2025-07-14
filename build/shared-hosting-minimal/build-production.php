<?php

declare(strict_types=1);

/**
 * Production Build Script for Shared Hosting
 * 
 * Creates minimal production-ready package without dev dependencies
 */

use function copy;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function rmdir;
use function scandir;
use function str_replace;
use function unlink;

echo "🚀 Building production package for shared hosting...\n\n";

// Configuration
$buildDir = __DIR__ . '/build-production';
$sourceDir = __DIR__;

// Essential directories to copy
$essentialDirs = [
    'config',
    'public',
    'src',
    'templates',
    'data',
    'log',
    'bin',
];

// Essential files to copy
$essentialFiles = [
    'composer.json',
    'composer.lock',
    '.htaccess',
    'README.md',
    'LICENSE',
];

// Files to exclude
$excludePatterns = [
    '.git',
    '.gitignore',
    'node_modules',
    'test',
    'tests',
    'phpunit.xml',
    'phpstan.neon',
    'phpcs.xml',
    'build-production.php',
    'docs',
    'CHANGELOG.md',
    'SECURITY.md',
    'OSSMETADATA',
    'package.json',
    'tsconfig.json',
    'vite.config.js',
];

/**
 * Recursively copy directory
 */
function copyDirectory(string $source, string $destination, array $excludePatterns = []): void
{
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $relativePath = str_replace($source . DIRECTORY_SEPARATOR, '', $item->getPathname());
        
        // Check if should be excluded
        $shouldExclude = false;
        foreach ($excludePatterns as $pattern) {
            if (str_contains($relativePath, $pattern)) {
                $shouldExclude = true;
                break;
            }
        }
        
        if ($shouldExclude) {
            continue;
        }

        $target = $destination . DIRECTORY_SEPARATOR . $relativePath;

        if ($item->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, 0755, true);
            }
        } else {
            copy($item->getPathname(), $target);
        }
    }
}

/**
 * Remove directory recursively
 */
function removeDirectory(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        is_dir($path) ? removeDirectory($path) : unlink($path);
    }
    rmdir($dir);
}

// Clean previous build
if (is_dir($buildDir)) {
    echo "🧹 Cleaning previous build...\n";
    removeDirectory($buildDir);
}

// Create build directory
mkdir($buildDir, 0755, true);

// Copy essential directories
echo "📁 Copying essential directories...\n";
foreach ($essentialDirs as $dir) {
    $sourcePath = $sourceDir . '/' . $dir;
    $targetPath = $buildDir . '/' . $dir;
    
    if (is_dir($sourcePath)) {
        echo "  - Copying {$dir}/\n";
        copyDirectory($sourcePath, $targetPath, $excludePatterns);
    }
}

// Copy essential files
echo "📄 Copying essential files...\n";
foreach ($essentialFiles as $file) {
    $sourcePath = $sourceDir . '/' . $file;
    $targetPath = $buildDir . '/' . $file;
    
    if (file_exists($sourcePath)) {
        echo "  - Copying {$file}\n";
        copy($sourcePath, $targetPath);
    }
}

// Create production composer.json (without dev dependencies)
echo "📦 Creating production composer.json...\n";
$composerContent = file_get_contents($buildDir . '/composer.json');
$composerData = json_decode($composerContent, true);

// Remove dev dependencies and scripts
unset($composerData['require-dev']);
unset($composerData['scripts']['test']);
unset($composerData['scripts']['phpstan']);
unset($composerData['scripts']['cs-check']);
unset($composerData['scripts']['cs-fix']);
unset($composerData['scripts']['static-analysis']);
unset($composerData['scripts']['twig-cs-check']);
unset($composerData['scripts']['twig-cs-fix']);
unset($composerData['scripts']['check']);

// Keep only essential scripts
$composerData['scripts'] = [
    'post-create-project-cmd' => $composerData['scripts']['post-create-project-cmd'] ?? [],
    'post-update-cmd' => $composerData['scripts']['post-update-cmd'] ?? [],
    'development-disable' => $composerData['scripts']['development-disable'] ?? '',
    'development-enable' => $composerData['scripts']['development-enable'] ?? '',
    'development-status' => $composerData['scripts']['development-status'] ?? '',
    'clear-config-cache' => $composerData['scripts']['clear-config-cache'] ?? '',
    'serve' => $composerData['scripts']['serve'] ?? '',
];

file_put_contents(
    $buildDir . '/composer.json',
    json_encode($composerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

// Create production .htaccess for shared hosting
echo "🔧 Creating production .htaccess...\n";
$htaccessContent = <<<'HTACCESS'
# Production .htaccess for shared hosting
RewriteEngine On

# Handle Angular and other front-end framework routes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/index.php [QSA,L]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
</IfModule>

# Disable server signature
ServerSignature Off

# Hide PHP version
<IfModule mod_headers.c>
    Header unset X-Powered-By
</IfModule>

# Prevent access to sensitive files
<FilesMatch "\.(env|log|ini|conf|config)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/webp "access plus 1 month"
</IfModule>

# Compress files
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
HTACCESS;

file_put_contents($buildDir . '/.htaccess', $htaccessContent);

// Create production README
echo "📖 Creating production README...\n";
$productionReadme = <<<'README'
# Production Build - Mezzio Light Application

This is a production-ready build optimized for shared hosting.

## Installation on Shared Hosting

1. Upload all files to your hosting directory
2. Run: `composer install --no-dev --optimize-autoloader`
3. Set document root to `/public` directory
4. Configure environment variables in `config/autoload/local.php`

## Configuration

Copy `config/autoload/local.php.dist` to `config/autoload/local.php` and configure:

```php
<?php
return [
    'debug' => false,
    'config_cache_enabled' => true,
    // Add your production configuration here
];
```

## File Permissions

Ensure these directories are writable:
- `data/cache/` (755)
- `log/` (755)

## Security

- All sensitive files are protected via .htaccess
- Debug mode is disabled
- Configuration cache is enabled
- Security headers are set

## Support

For support, please refer to the main project documentation.
README;

file_put_contents($buildDir . '/README.md', $productionReadme);

// Create deployment script
echo "🚀 Creating deployment script...\n";
$deployScript = <<<'DEPLOY'
#!/bin/bash

# Deployment script for shared hosting
echo "🚀 Deploying to production..."

# Install dependencies (production only)
composer install --no-dev --optimize-autoloader --no-interaction

# Clear cache
php bin/clear-config-cache.php

# Set permissions
chmod -R 755 data/cache/
chmod -R 755 log/

echo "✅ Deployment complete!"
echo "📁 Document root should point to: $(pwd)/public"
DEPLOY;

file_put_contents($buildDir . '/deploy.sh', $deployScript);
chmod($buildDir . '/deploy.sh', 0755);

echo "\n✅ Production build completed!\n";
echo "📦 Build location: {$buildDir}\n";
echo "📋 Next steps:\n";
echo "   1. Upload contents of build-production/ to your hosting\n";
echo "   2. Set document root to /public directory\n";
echo "   3. Run: composer install --no-dev --optimize-autoloader\n";
echo "   4. Configure config/autoload/local.php\n";
echo "   5. Run: chmod +x deploy.sh && ./deploy.sh\n\n";
echo "🎉 Ready for shared hosting deployment!\n";
