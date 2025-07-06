#!/bin/bash

# HDM Boot Protocol - Development Build Script
# 
# This script prepares the application for development environment
# by installing dependencies, setting up development mode, and
# preparing development assets.

set -e

# Configuration
BUILD_DIR="${BUILD_DIR:-./build/dev}"
APP_NAME="${APP_NAME:-mezzio-hdm-boot-protocol}"
VERSION="${VERSION:-dev-$(date +%Y%m%d_%H%M%S)}"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

error() {
    echo -e "${RED}❌ $1${NC}"
    exit 1
}

# Header
echo -e "${BLUE}"
echo "🚀 HDM Boot Protocol - Development Build"
echo "========================================"
echo -e "${NC}"

log "Starting development build process..."
log "Build directory: ${BUILD_DIR}"
log "Version: ${VERSION}"
echo

# Check prerequisites
log "Checking prerequisites..."

if ! command -v php &> /dev/null; then
    error "PHP is not installed or not in PATH"
fi

if ! command -v composer &> /dev/null; then
    error "Composer is not installed or not in PATH"
fi

PHP_VERSION=$(php -r "echo PHP_VERSION;")
log "PHP Version: ${PHP_VERSION}"

if ! php -m | grep -q "sqlite3"; then
    error "PHP SQLite3 extension is not installed"
fi

success "Prerequisites check passed"
echo

# Install/Update Composer dependencies
log "Installing/updating Composer dependencies..."
composer install --optimize-autoloader --no-interaction
success "Composer dependencies installed"
echo

# Enable development mode
log "Enabling development mode..."
composer development-enable
success "Development mode enabled"
echo

# Initialize databases
log "Initializing development databases..."
if [ -f "bin/init-all-db.php" ]; then
    php bin/init-all-db.php
    success "Databases initialized"
else
    warning "Database initialization script not found"
fi
echo

# Build themes (if available)
log "Building development themes..."
if [ -d "themes" ]; then
    if command -v pnpm &> /dev/null; then
        composer build:themes
        success "Themes built successfully"
    else
        warning "pnpm not found, skipping theme build"
    fi
else
    warning "Themes directory not found, skipping theme build"
fi
echo

# Create development build directory
log "Creating development build structure..."
mkdir -p "${BUILD_DIR}"

# Copy application files (excluding development files)
log "Copying application files..."
rsync -av --exclude-from=- . "${BUILD_DIR}/" << 'EOF'
.git/
.gitignore
node_modules/
tests/
phpunit.xml
phpcs.xml
phpstan.neon
.phpunit.result.cache
*.log
data/cache/*
data/sessions/*
config/autoload/*.local.php
.env
build/
vendor/
EOF

success "Application files copied"
echo

# Install production dependencies in build directory
log "Installing dependencies in build directory..."
cd "${BUILD_DIR}"
composer install --no-dev --optimize-autoloader --no-interaction
cd - > /dev/null
success "Build dependencies installed"
echo

# Set proper permissions
log "Setting proper permissions..."
find "${BUILD_DIR}" -type d -exec chmod 755 {} \;
find "${BUILD_DIR}" -type f -exec chmod 644 {} \;
chmod +x "${BUILD_DIR}/bin/"*.sh
chmod +x "${BUILD_DIR}/bin/"*.php
success "Permissions set"
echo

# Create version info
log "Creating version information..."
cat > "${BUILD_DIR}/VERSION" << EOF
HDM Boot Protocol - Development Build
=====================================
Version: ${VERSION}
Build Date: $(date)
PHP Version: ${PHP_VERSION}
Build Type: Development
EOF

success "Version information created"
echo

# Final summary
echo -e "${GREEN}"
echo "🎉 Development Build Complete!"
echo "=============================="
echo -e "${NC}"
echo "Build location: ${BUILD_DIR}"
echo "Version: ${VERSION}"
echo "Build type: Development"
echo
echo "Next steps:"
echo "1. Test the application: composer serve"
echo "2. Run tests: composer test"
echo "3. Check code quality: composer check"
echo
echo "Development build ready! 🚀"
