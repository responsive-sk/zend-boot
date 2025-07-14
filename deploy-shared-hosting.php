<?php

declare(strict_types=1);

/**
 * Shared Hosting Deployment Script
 * 
 * Creates a deployment package optimized for shared hosting environments
 * with limited functionality and permissions.
 */

use function copy;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function rmdir;
use function scandir;
use function unlink;

echo "🚀 Preparing deployment for shared hosting...\n\n";

class SharedHostingDeployer
{
    private string $buildDir = 'build-shared-hosting';
    private string $sourceDir;
    
    public function __construct()
    {
        $this->sourceDir = __DIR__;
    }
    
    public function deploy(): void
    {
        echo "📋 Checking compatibility...\n";
        $this->checkCompatibility();
        
        echo "🧹 Cleaning previous build...\n";
        $this->cleanPreviousBuild();
        
        echo "📁 Creating minimal build...\n";
        $this->createMinimalBuild();
        
        echo "⚙️  Generating configuration...\n";
        $this->generateConfiguration();
        
        echo "📄 Creating setup instructions...\n";
        $this->createSetupInstructions();
        
        echo "\n✅ Shared hosting package ready in: {$this->buildDir}/\n";
        echo "📖 See SHARED_HOSTING_SETUP.txt for upload instructions\n";
    }
    
    private function checkCompatibility(): void
    {
        $issues = [];
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            $issues[] = "PHP " . PHP_VERSION . " is too old. Minimum: 7.4.0";
        }
        
        // Check required extensions
        $required = ['json', 'mbstring'];
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $issues[] = "Missing required extension: {$ext}";
            }
        }
        
        // Check disabled functions
        $disabled = ini_get('disable_functions');
        if ($disabled) {
            $disabledList = explode(',', $disabled);
            $critical = ['file_get_contents', 'file_put_contents', 'mkdir'];
            foreach ($critical as $func) {
                if (in_array($func, $disabledList)) {
                    $issues[] = "Critical function disabled: {$func}";
                }
            }
        }
        
        if (!empty($issues)) {
            echo "⚠️  Compatibility issues found:\n";
            foreach ($issues as $issue) {
                echo "   - {$issue}\n";
            }
            echo "\n";
        } else {
            echo "✅ No compatibility issues found\n";
        }
    }
    
    private function cleanPreviousBuild(): void
    {
        if (is_dir($this->buildDir)) {
            $this->removeDirectory($this->buildDir);
        }
        mkdir($this->buildDir, 0755, true);
    }
    
    private function createMinimalBuild(): void
    {
        // Essential directories
        $essential = [
            'config' => 'config',
            'public' => 'public',
            'src' => 'src',
            'var' => 'var',
            'vendor' => 'vendor',
        ];
        
        foreach ($essential as $source => $target) {
            $sourcePath = $this->sourceDir . '/' . $source;
            $targetPath = $this->buildDir . '/' . $target;
            
            if (is_dir($sourcePath)) {
                echo "  - Copying {$source}/\n";
                $this->copyDirectory($sourcePath, $targetPath);
            }
        }
        
        // Essential files
        $files = [
            'composer.json',
            'composer.lock',
            'README.md',
            'LICENSE',
        ];
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                copy($file, $this->buildDir . '/' . $file);
            }
        }
    }
    
    private function generateConfiguration(): void
    {
        // Generate optimized .htaccess
        $htaccess = <<<'HTACCESS'
# Shared Hosting .htaccess with fallbacks
RewriteEngine On

# Try modern rewrite first
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Fallback for older Apache
<IfModule !mod_rewrite.c>
    ErrorDocument 404 /index.php
</IfModule>

# Security headers (if supported)
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# PHP settings (if allowed)
<IfModule mod_php.c>
    php_value memory_limit 256M
    php_value max_execution_time 60
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
</IfModule>

# Deny access to sensitive files
<Files "composer.*">
    Require all denied
</Files>
<Files "*.md">
    Require all denied
</Files>
HTACCESS;
        
        file_put_contents($this->buildDir . '/public/.htaccess', $htaccess);
        
        // Create var structure
        $varDirs = [
            'var/data',
            'var/cache/config',
            'var/cache/twig',
            'var/logs',
            'var/tmp',
            'var/sessions',
        ];
        
        foreach ($varDirs as $dir) {
            $fullPath = $this->buildDir . '/' . $dir;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
            
            // Add .gitignore to keep directories
            $gitignore = "*\n!.gitignore\n";
            file_put_contents($fullPath . '/.gitignore', $gitignore);
        }
    }
    
    private function createSetupInstructions(): void
    {
        $instructions = <<<'INSTRUCTIONS'
# Shared Hosting Setup Instructions

## Upload Instructions

1. **Upload files via FTP/cPanel File Manager:**
   - Upload ALL files from this build directory
   - Maintain the directory structure exactly as provided
   - Ensure public/ directory is your web root (or copy contents to web root)

2. **Set permissions (if possible):**
   ```
   chmod 755 var/
   chmod 755 var/data/
   chmod 755 var/cache/
   chmod 755 var/logs/
   chmod 755 var/tmp/
   chmod 755 var/sessions/
   ```

3. **Verify setup:**
   - Visit your website
   - Check that var/ directories are created automatically
   - Look for any error messages

## Troubleshooting

### "Permission denied" errors:
- Contact hosting provider to set correct permissions
- Some shared hosts don't allow chmod - this is usually OK

### "Function disabled" errors:
- Check with hosting provider about disabled PHP functions
- Most essential functions should work

### Memory limit errors:
- Contact hosting provider to increase memory_limit
- Or use a better hosting provider

### Rewrite errors:
- Ensure .htaccess is uploaded to public/ directory
- Check if mod_rewrite is enabled
- Some hosts require different rewrite rules

## File Structure

Your uploaded files should look like:
```
public_html/          (or your web root)
├── index.php
├── .htaccess
├── config/
├── src/
├── var/
│   ├── data/
│   ├── cache/
│   ├── logs/
│   └── tmp/
└── vendor/
```

## Support

If you encounter issues:
1. Check var/logs/ for error messages
2. Verify PHP version is 7.4+
3. Contact your hosting provider for assistance
4. Consider upgrading to a better hosting provider

This package is optimized for shared hosting but some providers
have severe limitations that may prevent proper operation.
INSTRUCTIONS;
        
        file_put_contents($this->buildDir . '/SHARED_HOSTING_SETUP.txt', $instructions);
    }
    
    private function copyDirectory(string $source, string $target): void
    {
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $targetPath = $target . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                copy($item->getPathname(), $targetPath);
            }
        }
    }
    
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}

// Run deployment
$deployer = new SharedHostingDeployer();
$deployer->deploy();
