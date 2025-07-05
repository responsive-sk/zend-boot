#!/bin/bash

# Build for Shared Hosting Script
# Creates production build optimized for shared hosting environments
# No Node.js, Git, or advanced tools required

set -e

# Configuration
BUILD_DIR="build/shared-hosting"
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
VERSION="shared-hosting-$TIMESTAMP"

echo "🏠 Building Mezzio Minimal for Shared Hosting..."

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

# Copy vendor directory (already built)
if [ -d "vendor" ]; then
    print_status "Copying vendor dependencies..."
    cp -r vendor/ "$BUILD_DIR/"
else
    print_error "Vendor directory not found. Run 'composer install' first."
    exit 1
fi

# Copy .htaccess files
cp .htaccess "$BUILD_DIR/" 2>/dev/null || true
cp public/.htaccess "$BUILD_DIR/public/" 2>/dev/null || true

# 3. Create shared hosting optimized configuration
print_status "Creating shared hosting configuration..."

# Create database configuration for SQLite
cat > "$BUILD_DIR/config/autoload/database.local.php" << 'EOF'
<?php

declare(strict_types=1);

// Shared Hosting Database Configuration - SQLite
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

// Shared Hosting Session Configuration - File-based
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

# 4. Create data directories and databases
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

# 5. Create optimized .htaccess for shared hosting
print_status "Creating optimized .htaccess files..."

# Main .htaccess
cat > "$BUILD_DIR/.htaccess" << 'EOF'
# Shared Hosting .htaccess Configuration
# Redirect all requests to public directory

RewriteEngine On

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Redirect to public directory
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ /public/$1 [L,QSA]

# Deny access to sensitive files
<FilesMatch "\.(env|ini|log|sh|sql|conf|bak)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Deny access to directories
RedirectMatch 403 ^/(config|data|logs|src|vendor|modules)/.*$
EOF

# Public .htaccess
cat > "$BUILD_DIR/public/.htaccess" << 'EOF'
# Public Directory .htaccess for Shared Hosting

RewriteEngine On

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# Handle Angular/React routes (if needed)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]

# Optimize caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
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

# Deny access to sensitive files
<FilesMatch "\.(env|ini|log|sh|sql|conf|bak)$">
    Order allow,deny
    Deny from all
</FilesMatch>
EOF

# 6. Remove development files
print_status "Removing development files..."
rm -rf "$BUILD_DIR/tests" 2>/dev/null || true
rm -f "$BUILD_DIR/phpstan.neon" "$BUILD_DIR/rector.php" "$BUILD_DIR/phpcs.xml" 2>/dev/null || true
rm -f "$BUILD_DIR/bin/build-"*.sh 2>/dev/null || true

# 7. Set proper permissions
print_status "Setting proper permissions..."
find "$BUILD_DIR" -type d -exec chmod 755 {} \; 2>/dev/null || true
find "$BUILD_DIR" -type f -exec chmod 644 {} \; 2>/dev/null || true
chmod 755 "$BUILD_DIR/public" 2>/dev/null || true
chmod 644 "$BUILD_DIR/public/index.php" 2>/dev/null || true
chmod 755 "$BUILD_DIR/data" "$BUILD_DIR/data/sessions" "$BUILD_DIR/data/cache" "$BUILD_DIR/logs" 2>/dev/null || true

# 8. Create deployment instructions
print_status "Creating deployment instructions..."
cat > "$BUILD_DIR/SHARED_HOSTING_DEPLOY.md" << 'EOF'
# Shared Hosting Deployment Instructions

## Quick Start

1. **Upload Files**
   - Upload all files from this directory to your hosting account
   - Set document root to the `public/` directory

2. **Set Permissions**
   ```
   chmod 755 data/ data/sessions/ data/cache/ logs/
   chmod 644 data/*.db (if present)
   ```

3. **Test Installation**
   - Visit your website
   - Try logging in with default users (if databases exist)

## Default Users (if databases are included)

- **Admin**: admin / admin123
- **User**: user / user123  
- **Mark**: mark / mark123

## Configuration

### Database
- Uses SQLite databases in `data/` directory
- No MySQL setup required
- Databases created automatically on first run

### Sessions
- File-based sessions in `data/sessions/`
- No Redis or special session storage needed

### Security
- HTTPS recommended (update session.local.php if using HTTPS)
- Change default passwords after first login

## Troubleshooting

### Permission Issues
```bash
chmod -R 755 data/ logs/
chmod -R 644 config/autoload/*.local.php
```

### Database Issues
- Check if `data/` directory is writable
- Ensure SQLite extension is enabled in PHP

### Routing Issues
- Verify `.htaccess` files are uploaded
- Check if mod_rewrite is enabled

## Support

- Check `logs/` directory for error messages
- Ensure PHP 8.1+ is available
- Verify all required PHP extensions are installed

EOF

# 9. Create build info
cat > "$BUILD_DIR/BUILD_INFO.txt" << EOF
Shared Hosting Build Information
===============================
Build Type: Shared Hosting Optimized
Version: $VERSION
Build Date: $(date '+%Y-%m-%d %H:%M:%S')
PHP Version: $(php -v | head -n 1)

Features:
✅ SQLite databases (no MySQL required)
✅ File-based sessions (no Redis required)
✅ Optimized .htaccess files
✅ Pre-configured for shared hosting
✅ No Node.js/npm dependencies
✅ Ready to upload and run

Directory Structure:
$(find "$BUILD_DIR" -maxdepth 2 -type d | sort)

Size Information:
- Total: $(du -sh "$BUILD_DIR" | cut -f1)
- Vendor: $(du -sh "$BUILD_DIR/vendor" 2>/dev/null | cut -f1 || echo "N/A")
- Public: $(du -sh "$BUILD_DIR/public" 2>/dev/null | cut -f1 || echo "N/A")

Deployment:
1. Upload all files to your hosting account
2. Set document root to public/ directory
3. Set proper permissions (see SHARED_HOSTING_DEPLOY.md)
4. Visit your website
EOF

# 10. Show results
echo ""
print_success "Shared hosting build completed successfully!"
echo ""
echo "📊 Build Summary:"
echo "   - Build Type: Shared Hosting Optimized"
echo "   - Version: $VERSION"
echo "   - Location: $BUILD_DIR"
echo "   - Size: $(du -sh "$BUILD_DIR" | cut -f1)"
echo ""
print_success "🏠 Ready for shared hosting deployment!"
echo ""
echo "📋 Next Steps:"
echo "   1. Upload contents of $BUILD_DIR to your hosting account"
echo "   2. Set document root to public/ directory"
echo "   3. Follow instructions in SHARED_HOSTING_DEPLOY.md"
echo ""
print_warning "📄 See $BUILD_DIR/SHARED_HOSTING_DEPLOY.md for detailed deployment instructions"
