#!/bin/bash

# Build to Directory Script for Mezzio Minimal
# Creates production build in build/ directory

set -e

# Configuration
BUILD_TYPE=${1:-production}  # production, staging, or release
BUILD_DIR="build/$BUILD_TYPE"
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
VERSION=$(git describe --tags --always 2>/dev/null || echo "v1.0.0-$TIMESTAMP")

echo "🚀 Building Mezzio Minimal to $BUILD_DIR..."

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

# 1. Clean and prepare build directory
print_status "Preparing build directory: $BUILD_DIR"
rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR"

# 2. Copy source files (exclude build directory)
print_status "Copying source files..."
cp -r config/ "$BUILD_DIR/"
cp -r public/ "$BUILD_DIR/"
cp -r src/ "$BUILD_DIR/"
cp -r themes/ "$BUILD_DIR/"
cp composer.json "$BUILD_DIR/"
cp composer.lock "$BUILD_DIR/" 2>/dev/null || true

# Copy .htaccess and SEO files
cp .htaccess "$BUILD_DIR/" 2>/dev/null || true
cp public/.htaccess "$BUILD_DIR/public/" 2>/dev/null || true
cp public/robots.txt "$BUILD_DIR/public/" 2>/dev/null || true
cp public/sitemap.xml "$BUILD_DIR/public/" 2>/dev/null || true

# 3. Install dependencies based on build type
cd "$BUILD_DIR"

if [ "$BUILD_TYPE" = "production" ] || [ "$BUILD_TYPE" = "shared-hosting-minimal" ]; then
    print_status "Installing production dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
elif [ "$BUILD_TYPE" = "staging" ]; then
    print_status "Installing staging dependencies..."
    composer install --optimize-autoloader --no-interaction
else
    print_status "Installing all dependencies..."
    composer install --no-interaction
fi

# 4. Build themes
print_status "Building themes..."

# Bootstrap theme
cd themes/bootstrap
if [ ! -d "node_modules" ]; then
    print_status "Installing Bootstrap theme dependencies..."
    pnpm install
fi

# Fix esbuild permissions if needed
find node_modules -name "esbuild" -type f -exec chmod +x {} \; 2>/dev/null || true

if [ "$BUILD_TYPE" = "production" ]; then
    pnpm run build:prod
else
    pnpm run build
fi
cd ../..

# Main theme
cd themes/main
if [ ! -d "node_modules" ]; then
    print_status "Installing Main theme dependencies..."
    pnpm install
fi

# Fix esbuild permissions if needed
find node_modules -name "esbuild" -type f -exec chmod +x {} \; 2>/dev/null || true

if [ "$BUILD_TYPE" = "production" ]; then
    pnpm run build:prod
else
    pnpm run build
fi
cd ../..

# 5. Optimize for production
if [ "$BUILD_TYPE" = "production" ] || [ "$BUILD_TYPE" = "shared-hosting-minimal" ]; then
    print_status "Optimizing for production..."

    # Remove development files
    rm -rf tests/ 2>/dev/null || true
    rm -f phpstan.neon rector.php phpcs.xml 2>/dev/null || true
    rm -f build-*.sh 2>/dev/null || true

    # Clean theme directories
    rm -rf themes/*/node_modules 2>/dev/null || true
    rm -f themes/*/pnpm-lock.yaml 2>/dev/null || true

    # Optimize vendor
    find vendor/ -type d -name "docs" -exec rm -rf {} \; 2>/dev/null || true
    find vendor/ -type d -name "test" -exec rm -rf {} \; 2>/dev/null || true
    find vendor/ -type d -name "tests" -exec rm -rf {} \; 2>/dev/null || true
    find vendor/ -type d -name "Test" -exec rm -rf {} \; 2>/dev/null || true
    find vendor/ -name "*.md" -delete 2>/dev/null || true
    find vendor/ -name "LICENSE*" -delete 2>/dev/null || true
    find vendor/ -name "README*" -delete 2>/dev/null || true
    find vendor/ -name "CHANGELOG*" -delete 2>/dev/null || true
    find vendor/ -name "UPGRADE*" -delete 2>/dev/null || true

    # Generate optimized autoloader
    composer dump-autoload --optimize --no-dev --classmap-authoritative
fi

# 5.1. Additional optimizations for shared-hosting-minimal
if [ "$BUILD_TYPE" = "shared-hosting-minimal" ]; then
    print_status "Applying minimal hosting optimizations..."

    # Remove additional development artifacts
    rm -rf themes/*/src/ 2>/dev/null || true
    rm -f themes/*/package.json 2>/dev/null || true
    rm -f themes/*/vite.config.js 2>/dev/null || true
    rm -f themes/*/tailwind.config.js 2>/dev/null || true
    rm -f themes/*/postcss.config.js 2>/dev/null || true

    # Remove vendor binaries (not needed in shared hosting)
    rm -rf vendor/bin/ 2>/dev/null || true

    # Remove additional vendor documentation
    find vendor/ -name "CHANGELOG*" -delete 2>/dev/null || true
    find vendor/ -name "UPGRADE*" -delete 2>/dev/null || true
    find vendor/ -name "*.dist" -delete 2>/dev/null || true

    # Remove empty directories
    find vendor/ -type d -empty -delete 2>/dev/null || true
fi

# 6. Create build info
print_status "Creating build information..."
cat > BUILD_INFO.txt << EOF
Build Information
=================
Build Type: $BUILD_TYPE
Version: $VERSION
Build Date: $(date '+%Y-%m-%d %H:%M:%S')
Git Hash: $(git rev-parse --short HEAD 2>/dev/null || echo "unknown")
PHP Version: $(php -v | head -n 1)

Directory Structure:
$(find . -maxdepth 2 -type d | sort)

Theme Assets:
$(find public/themes -name "*.css" -o -name "*.js" 2>/dev/null | sort)

Size Information:
- Vendor: $(du -sh vendor/ 2>/dev/null | cut -f1 || echo "N/A")
- Public: $(du -sh public/ 2>/dev/null | cut -f1 || echo "N/A")
- Total: $(du -sh . 2>/dev/null | cut -f1 || echo "N/A")

Build Features:
✅ Versioned assets with hash
✅ Apache .htaccess configuration
✅ Optimized autoloader
$([ "$BUILD_TYPE" = "production" ] && echo "✅ Production optimized" || [ "$BUILD_TYPE" = "shared-hosting-minimal" ] && echo "✅ Minimal hosting optimized" || echo "⚠️  Development build")
EOF

# 7. Set proper permissions
print_status "Setting proper permissions..."
# Set directory permissions
find . -type d -exec chmod 755 {} \; 2>/dev/null || true
# Set file permissions
find . -type f -exec chmod 644 {} \; 2>/dev/null || true
# Make public directory and index.php executable
chmod 755 public/ 2>/dev/null || true
chmod 644 public/index.php 2>/dev/null || true
# Ensure .htaccess files are readable
find . -name ".htaccess" -exec chmod 644 {} \; 2>/dev/null || true

# 8. Return to original directory
cd - > /dev/null

# 9. Create release archive if requested
if [ "$BUILD_TYPE" = "release" ] || [ "$2" = "archive" ]; then
    print_status "Creating release archive..."
    ARCHIVE_NAME="mezzio-minimal-$VERSION.tar.gz"
    tar -czf "build/releases/$ARCHIVE_NAME" -C "$BUILD_DIR" .
    print_success "Release archive created: build/releases/$ARCHIVE_NAME"
fi

# 10. Show results
echo ""
print_success "Build completed successfully!"
echo ""
echo "📊 Build Summary:"
echo "   - Build Type: $BUILD_TYPE"
echo "   - Version: $VERSION"
echo "   - Location: $BUILD_DIR"
echo "   - Size: $(du -sh "$BUILD_DIR" | cut -f1)"
echo ""

if [ "$BUILD_TYPE" = "production" ]; then
    print_success "🚀 Production build ready for deployment!"
    echo "   - Upload contents of $BUILD_DIR to your server"
    echo "   - Point web server document root to $BUILD_DIR/public/"
elif [ "$BUILD_TYPE" = "shared-hosting-minimal" ]; then
    print_success "🏠 Minimal shared hosting build ready!"
    echo "   - Upload contents of $BUILD_DIR to your shared hosting"
    echo "   - Configure document root to point to public/ directory"
    echo "   - Set open_basedir restriction if available"
elif [ "$BUILD_TYPE" = "staging" ]; then
    print_success "🧪 Staging build ready for testing!"
else
    print_success "📦 Development build ready!"
fi

echo ""
print_warning "📄 See $BUILD_DIR/BUILD_INFO.txt for detailed information"
