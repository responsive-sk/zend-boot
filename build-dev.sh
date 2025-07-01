#!/bin/bash

# Development Build Script for Mezzio Minimal
# Quick build for development with non-hashed assets

set -e

echo "🛠️  Starting development build for Mezzio Minimal..."

# Colors for output
BLUE='\033[0;34m'
GREEN='\033[0;32m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

# 1. Clean previous builds
print_status "Cleaning previous builds..."
rm -rf public/themes/*/assets/
rm -rf public/themes/*/.vite/

# 2. Install PHP dependencies for development
print_status "Installing PHP dependencies for development..."
composer install

# 3. Build themes for development (no hash)
print_status "Building Bootstrap theme (dev mode)..."
cd themes/bootstrap
if [ ! -d "node_modules" ]; then
    print_status "Installing Bootstrap theme dependencies..."
    pnpm install
fi
pnpm run build
cd ../..

print_status "Building Main theme (dev mode)..."
cd themes/main
if [ ! -d "node_modules" ]; then
    print_status "Installing Main theme dependencies..."
    pnpm install
fi
pnpm run build
cd ../..

# 4. Show results
echo ""
print_success "Development build completed!"
echo ""
echo "📊 Development Build Summary:"
echo "   - Theme assets: $(find public/themes -name "*.css" -o -name "*.js" | wc -l) files"
echo "   - Public themes: $(du -sh public/themes/ | cut -f1)"
echo ""
print_success "✅ Development assets ready (no hash for easy debugging)"
print_success "✅ All dev dependencies available"
echo ""
print_success "🚀 Ready for development!"
