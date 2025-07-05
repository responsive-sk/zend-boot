#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Production Build Script for Shared Hosting
 * 
 * Creates a minimal, optimized build ready for shared hosting deployment
 */

echo "🚀 Building production package for shared hosting...\n\n";

$buildDir = __DIR__ . '/../build';
$sourceDir = __DIR__ . '/..';

// Clean build directory
if (is_dir($buildDir)) {
    echo "🧹 Cleaning build directory...\n";
    exec("rm -rf $buildDir");
}

mkdir($buildDir, 0755, true);

echo "📦 Copying essential files...\n";

// Copy essential directories (excluding vendor)
$essentialDirs = [
    'public',
    'config',
    'src',
    'modules',
    'templates'
];

foreach ($essentialDirs as $dir) {
    if (is_dir("$sourceDir/$dir")) {
        echo "  📁 Copying $dir/\n";
        exec("cp -r $sourceDir/$dir $buildDir/");
    }
}

// Copy essential files
$essentialFiles = [
    'composer.json',
    'composer.lock',
    '.htaccess'
];

foreach ($essentialFiles as $file) {
    if (file_exists("$sourceDir/$file")) {
        echo "  📄 Copying $file\n";
        copy("$sourceDir/$file", "$buildDir/$file");
    }
}

// Create data directory
echo "📁 Creating data directory...\n";
mkdir("$buildDir/data", 0755, true);
mkdir("$buildDir/logs", 0755, true);

// Install production dependencies
echo "📦 Installing production dependencies...\n";
exec("cd $buildDir && composer install --no-dev --optimize-autoloader --no-interaction 2>&1", $output, $returnCode);

if ($returnCode !== 0) {
    echo "❌ Composer install failed:\n";
    echo implode("\n", $output) . "\n";
    exit(1);
}

echo "  ✅ Dependencies installed\n";

// Copy databases if they exist
if (file_exists("$sourceDir/data/user.db")) {
    copy("$sourceDir/data/user.db", "$buildDir/data/user.db");
    echo "  📊 Copied user.db\n";
}

if (file_exists("$sourceDir/data/mark.db")) {
    copy("$sourceDir/data/mark.db", "$buildDir/data/mark.db");
    echo "  📊 Copied mark.db\n";
}

// Create production config overrides
echo "⚙️  Creating production configuration...\n";

// Production database config
file_put_contents("$buildDir/config/autoload/database.local.php", '<?php
return [
    "database" => [
        "user" => [
            "driver" => "sqlite",
            "database" => __DIR__ . "/../../data/user.db",
        ],
        "mark" => [
            "driver" => "sqlite", 
            "database" => __DIR__ . "/../../data/mark.db",
        ],
    ],
];
');

// Production session config
file_put_contents("$buildDir/config/autoload/session.local.php", '<?php
return [
    "session" => [
        "cookie_secure" => true,
        "cookie_httponly" => true,
        "cookie_samesite" => "Strict",
        "ini_settings" => [
            "session.gc_maxlifetime" => 3600,
            "session.cookie_lifetime" => 0,
            "session.use_strict_mode" => 1,
        ],
    ],
];
');

// Create .htaccess for shared hosting
echo "🔧 Creating .htaccess for shared hosting...\n";
file_put_contents("$buildDir/public/.htaccess", 'RewriteEngine On

# Handle Angular and other front-end routes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Deny access to sensitive files
<FilesMatch "\.(env|log|ini)$">
    Require all denied
</FilesMatch>

# Deny access to directories
RedirectMatch 404 ^/(config|data|logs|vendor|src|modules)/
');

// Create root .htaccess to redirect to public
file_put_contents("$buildDir/.htaccess", 'RewriteEngine On
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ public/$1 [L]
');

// Create deployment instructions
echo "📋 Creating deployment instructions...\n";
file_put_contents("$buildDir/DEPLOY.md", '# Shared Hosting Deployment Instructions

## Upload Files
1. Upload all files to your hosting account
2. Set document root to `/public/` directory (or upload public/ contents to public_html/)

## File Permissions
```bash
chmod 755 data/
chmod 755 logs/
chmod 644 data/*.db
```

## Database Setup
Databases are already included (user.db, mark.db) with default users:
- admin/admin123 (admin role)
- user/user123 (user role)
- mark/mark123 (mark role)

## Test URLs
- `/` - Home page
- `/user/login` - Login page
- `/user/dashboard` - Protected dashboard

## Troubleshooting
- Check file permissions if you get 500 errors
- Ensure PHP 8.1+ is available
- Check error logs in logs/ directory

## Security Notes
- Change default passwords after deployment
- Enable HTTPS in production
- Review session.local.php settings
');

// Create archive
echo "📦 Creating deployment archive...\n";
$archiveName = "mezzio-user-app-" . date('Y-m-d-H-i-s') . ".tar.gz";
exec("cd $buildDir && tar -czf ../$archiveName .");

$archivePath = dirname($buildDir) . "/$archiveName";
$archiveSize = round(filesize($archivePath) / 1024 / 1024, 2);

echo "\n✅ Production build completed!\n\n";
echo "📦 Archive: $archiveName ($archiveSize MB)\n";
echo "📁 Build directory: $buildDir\n";
echo "📋 Deployment instructions: $buildDir/DEPLOY.md\n\n";

echo "🚀 Ready for shared hosting deployment!\n";
echo "   1. Download: $archiveName\n";
echo "   2. Upload to your hosting account\n";
echo "   3. Extract files\n";
echo "   4. Set document root to public/ directory\n";
echo "   5. Test login with admin/admin123\n\n";
