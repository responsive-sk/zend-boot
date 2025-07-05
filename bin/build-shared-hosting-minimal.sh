#!/bin/bash

# Minimal Build for Shared Hosting Script
# Creates ultra-minimal production build optimized for shared hosting
# Removes all tests, docs, examples, and development files

set -e

# Configuration
BUILD_DIR="build/shared-hosting-minimal"
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
VERSION="minimal-$TIMESTAMP"

echo "🏠 Building MINIMAL Mezzio for Shared Hosting..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 1. Clean and prepare build directory
print_status "Preparing build directory: $BUILD_DIR"
rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR"

# 2. Copy source files
print_status "Copying source files..."
cp -r config/ "$BUILD_DIR/"
cp -r public/ "$BUILD_DIR/"
cp -r src/ "$BUILD_DIR/"
cp -r modules/ "$BUILD_DIR/"
cp -r templates/ "$BUILD_DIR/"
cp composer.json "$BUILD_DIR/"
cp composer.lock "$BUILD_DIR/" 2>/dev/null || true

# 3. Install minimal production dependencies
print_status "Installing minimal production dependencies..."
cd "$BUILD_DIR"
composer install --no-dev --optimize-autoloader --no-interaction --classmap-authoritative

# 4. Aggressively clean vendor directory for shared hosting
print_status "Aggressively optimizing vendor for shared hosting..."

# Remove test directories
find vendor/ -type d -name "test" -exec rm -rf {} + 2>/dev/null || true
find vendor/ -type d -name "tests" -exec rm -rf {} + 2>/dev/null || true
find vendor/ -type d -name "Test" -exec rm -rf {} + 2>/dev/null || true
find vendor/ -type d -name "Tests" -exec rm -rf {} + 2>/dev/null || true
find vendor/ -type d -name "testing" -exec rm -rf {} + 2>/dev/null || true

# Remove documentation
find vendor/ -type d -name "doc" -exec rm -rf {} + 2>/dev/null || true
find vendor/ -type d -name "docs" -exec rm -rf {} + 2>/dev/null || true
find vendor/ -type d -name "documentation" -exec rm -rf {} + 2>/dev/null || true

# Remove example directories
find vendor/ -type d -name "example" -exec rm -rf {} + 2>/dev/null || true
find vendor/ -type d -name "examples" -exec rm -rf {} + 2>/dev/null || true
find vendor/ -type d -name "sample" -exec rm -rf {} + 2>/dev/null || true
find vendor/ -type d -name "samples" -exec rm -rf {} + 2>/dev/null || true
find vendor/ -type d -name "demo" -exec rm -rf {} + 2>/dev/null || true
find vendor/ -type d -name "demos" -exec rm -rf {} + 2>/dev/null || true

# Remove development and CI files
find vendor/ -name "*.md" -delete 2>/dev/null || true
find vendor/ -name "README*" -delete 2>/dev/null || true
find vendor/ -name "CHANGELOG*" -delete 2>/dev/null || true
find vendor/ -name "LICENSE*" -delete 2>/dev/null || true
find vendor/ -name "CONTRIBUTING*" -delete 2>/dev/null || true
find vendor/ -name "phpunit.xml*" -delete 2>/dev/null || true
find vendor/ -name "phpcs.xml*" -delete 2>/dev/null || true
find vendor/ -name "phpstan.neon*" -delete 2>/dev/null || true
find vendor/ -name "psalm.xml*" -delete 2>/dev/null || true
find vendor/ -name ".travis.yml" -delete 2>/dev/null || true
find vendor/ -name ".github" -type d -exec rm -rf {} + 2>/dev/null || true
find vendor/ -name ".git*" -delete 2>/dev/null || true
find vendor/ -name "Makefile" -delete 2>/dev/null || true
find vendor/ -name "makefile" -delete 2>/dev/null || true

# Remove composer files from vendor packages
find vendor/ -name "composer.json" -delete 2>/dev/null || true
find vendor/ -name "composer.lock" -delete 2>/dev/null || true

# Remove package.json and node files
find vendor/ -name "package.json" -delete 2>/dev/null || true
find vendor/ -name "package-lock.json" -delete 2>/dev/null || true
find vendor/ -name "yarn.lock" -delete 2>/dev/null || true

# Remove IDE files
find vendor/ -name ".idea" -type d -exec rm -rf {} + 2>/dev/null || true
find vendor/ -name ".vscode" -type d -exec rm -rf {} + 2>/dev/null || true

# Generate final optimized autoloader
print_status "Generating optimized autoloader..."
composer dump-autoload --optimize --no-dev --classmap-authoritative

cd - > /dev/null

# 5. Create shared hosting optimized configuration
print_status "Creating shared hosting configuration..."

# Create database configuration for SQLite
cat > "$BUILD_DIR/config/autoload/database.local.php" << 'EOF'
<?php

declare(strict_types=1);

// Minimal Shared Hosting Database Configuration - SQLite
return [
    'database' => [
        'user' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../../data/user.db',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
        'mark' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../../data/mark.db',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
        'system' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../../data/system.db',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
    ],
];
EOF

# Create session configuration for file-based sessions
cat > "$BUILD_DIR/config/autoload/session.local.php" << 'EOF'
<?php

declare(strict_types=1);

// Minimal Shared Hosting Session Configuration - File-based
return [
    'session' => [
        'cookie_name' => 'MEZZIO_SESSION',
        'cookie_secure' => false,  // Set to true if using HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'cookie_lifetime' => 3600,  // 1 hour
        'cookie_path' => '/',
        'cookie_domain' => '',
        
        'ini_settings' => [
            'session.save_handler' => 'files',
            'session.save_path' => __DIR__ . '/../../data/sessions',
            'session.gc_maxlifetime' => 3600,
            'session.gc_probability' => 1,
            'session.gc_divisor' => 100,
            'session.use_strict_mode' => 1,
            'session.use_cookies' => 1,
            'session.use_only_cookies' => 1,
            'session.cookie_httponly' => 1,
            'session.cookie_samesite' => 'Strict',
        ],
    ],
];
EOF

# 6. Create data directories and databases
print_status "Creating data directories and databases..."
mkdir -p "$BUILD_DIR/data/sessions"
mkdir -p "$BUILD_DIR/data/cache"
mkdir -p "$BUILD_DIR/logs"

# Copy existing databases if they exist
if [ -f "data/user.db" ]; then
    cp data/user.db "$BUILD_DIR/data/"
    print_success "Copied existing user.db"
else
    print_warning "user.db not found - will be created on first run"
fi

if [ -f "data/mark.db" ]; then
    cp data/mark.db "$BUILD_DIR/data/"
    print_success "Copied existing mark.db"
else
    print_warning "mark.db not found - will be created on first run"
fi

if [ -f "data/system.db" ]; then
    cp data/system.db "$BUILD_DIR/data/"
    print_success "Copied existing system.db"
else
    print_warning "system.db not found - will be created on first run"
fi

# 7. Remove ALL unnecessary files for shared hosting
print_status "Removing ALL unnecessary files for shared hosting..."
rm -rf "$BUILD_DIR/tests" 2>/dev/null || true
rm -f "$BUILD_DIR/phpstan.neon" "$BUILD_DIR/rector.php" "$BUILD_DIR/phpcs.xml" 2>/dev/null || true
# Keep composer.json for autoloader PSR-4 mapping, but remove composer.lock
rm -f "$BUILD_DIR/composer.lock" 2>/dev/null || true

# Remove any remaining development directories
rm -rf "$BUILD_DIR/var" 2>/dev/null || true
rm -rf "$BUILD_DIR/tmp" 2>/dev/null || true

# 8. Create ultra-minimal .htaccess
print_status "Creating ultra-minimal .htaccess files..."

# Main .htaccess
cat > "$BUILD_DIR/.htaccess" << 'EOF'
# Minimal Shared Hosting .htaccess
RewriteEngine On
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ /public/$1 [L,QSA]
<FilesMatch "\.(env|ini|log|sh|sql|conf|bak|db)$">
    Order allow,deny
    Deny from all
</FilesMatch>
RedirectMatch 403 ^/(config|data|logs|src|vendor|modules)/.*$
EOF

# Public .htaccess
cat > "$BUILD_DIR/public/.htaccess" << 'EOF'
# Minimal Public .htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
<FilesMatch "\.(env|ini|log|sh|sql|conf|bak|db)$">
    Order allow,deny
    Deny from all
</FilesMatch>
EOF

# 9. Set proper permissions
print_status "Setting proper permissions..."
find "$BUILD_DIR" -type d -exec chmod 755 {} \; 2>/dev/null || true
find "$BUILD_DIR" -type f -exec chmod 644 {} \; 2>/dev/null || true
chmod 755 "$BUILD_DIR/data" "$BUILD_DIR/data/sessions" "$BUILD_DIR/data/cache" "$BUILD_DIR/logs" 2>/dev/null || true

# 10. Create minimal deployment instructions
cat > "$BUILD_DIR/MINIMAL_DEPLOY.md" << 'EOF'
# MINIMAL Shared Hosting Deployment

## Ultra-Quick Deploy
1. Upload all files to your hosting account
2. Set document root to `public/` directory
3. Done!

## Default Users
- admin/admin123 (admin)
- user/user123 (user)
- mark/mark123 (mark)

## Features
- SQLite databases (no MySQL needed)
- File sessions (no Redis needed)
- Minimal vendor (no tests/docs)
- Ultra-small footprint

## Troubleshooting
- Ensure PHP 8.1+ is available
- Check if `data/` directory is writable
- Verify mod_rewrite is enabled
EOF

# 11. Create build info
VENDOR_SIZE=$(du -sh "$BUILD_DIR/vendor" 2>/dev/null | cut -f1 || echo "N/A")
TOTAL_SIZE=$(du -sh "$BUILD_DIR" 2>/dev/null | cut -f1 || echo "N/A")

cat > "$BUILD_DIR/BUILD_INFO.txt" << EOF
MINIMAL Shared Hosting Build
===========================
Build Type: Ultra-Minimal Shared Hosting
Version: $VERSION
Build Date: $(date '+%Y-%m-%d %H:%M:%S')
PHP Version: $(php -v | head -n 1)

Optimizations:
✅ Minimal vendor (no tests, docs, examples, CI files)
✅ Removed ALL development files
✅ Optimized autoloader with classmap-authoritative
✅ Ultra-minimal .htaccess files
✅ SQLite databases with existing users
✅ File-based sessions

Size Information:
- Vendor: $VENDOR_SIZE
- Total: $TOTAL_SIZE

Ready for immediate upload to shared hosting!
EOF

# 12. Show results
echo ""
print_success "MINIMAL shared hosting build completed!"
echo ""
echo "📊 Build Summary:"
echo "   - Build Type: Ultra-Minimal Shared Hosting"
echo "   - Version: $VERSION"
echo "   - Location: $BUILD_DIR"
echo "   - Size: $TOTAL_SIZE"
echo "   - Vendor: $VENDOR_SIZE"
echo ""
print_success "🏠 Ultra-minimal build ready for shared hosting!"
echo ""
print_warning "📄 See $BUILD_DIR/MINIMAL_DEPLOY.md for deployment instructions"
