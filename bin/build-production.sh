#!/bin/bash

# HDM Boot Protocol - Production Build Script
# 
# This script prepares the application for production deployment
# by optimizing dependencies, clearing development files, and
# creating a production-ready package.

set -e

# Configuration
BUILD_DIR="${BUILD_DIR:-./build}"
PACKAGE_NAME="${PACKAGE_NAME:-mezzio-hdm-boot-protocol}"
VERSION="${VERSION:-$(date +%Y%m%d_%H%M%S)}"
EXCLUDE_PATTERNS=(
    ".git"
    ".gitignore"
    "node_modules"
    "tests"
    "phpunit.xml"
    "phpcs.xml"
    "phpstan.neon"
    ".phpunit.result.cache"
    "composer.lock"
    "*.log"
    "data/cache/*"
    "data/sessions/*"
    "config/autoload/*.local.php"
    ".env"
    "build"
    "docs/screenshots"
    "*.md"
)

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

error() {
    echo -e "${RED}[ERROR]${NC} $1" >&2
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Clean previous build
clean_build() {
    log "Cleaning previous build..."
    
    if [ -d "$BUILD_DIR" ]; then
        rm -rf "$BUILD_DIR"
    fi
    
    mkdir -p "$BUILD_DIR"
    success "Build directory cleaned"
}

# Install production dependencies
install_production_dependencies() {
    log "Installing production dependencies..."
    
    # Backup current composer.lock
    if [ -f "composer.lock" ]; then
        cp composer.lock composer.lock.backup
    fi
    
    # Install production dependencies
    composer install --no-dev --optimize-autoloader --no-interaction
    
    success "Production dependencies installed"
}

# Copy application files
copy_application_files() {
    log "Copying application files..."
    
    # Create rsync exclude pattern
    local exclude_args=""
    for pattern in "${EXCLUDE_PATTERNS[@]}"; do
        exclude_args="$exclude_args --exclude=$pattern"
    done
    
    # Copy files using rsync
    rsync -av $exclude_args ./ "$BUILD_DIR/"
    
    success "Application files copied"
}

# Create production configuration templates
create_production_configs() {
    log "Creating production configuration templates..."
    
    # Ensure config templates exist in build
    if [ ! -f "$BUILD_DIR/config/autoload/database.local.php.dist" ]; then
        warning "database.local.php.dist not found in build"
    fi
    
    if [ ! -f "$BUILD_DIR/config/autoload/session.local.php.dist" ]; then
        warning "session.local.php.dist not found in build"
    fi
    
    if [ ! -f "$BUILD_DIR/.env.dist" ]; then
        warning ".env.dist not found in build"
    fi
    
    success "Production configuration templates ready"
}

# Optimize autoloader
optimize_autoloader() {
    log "Optimizing autoloader..."
    
    cd "$BUILD_DIR"
    composer dump-autoload --optimize --no-dev
    cd - > /dev/null
    
    success "Autoloader optimized"
}

# Create necessary directories
create_directories() {
    log "Creating necessary directories..."
    
    mkdir -p "$BUILD_DIR/logs"
    mkdir -p "$BUILD_DIR/data/cache"
    mkdir -p "$BUILD_DIR/data/sessions"
    
    # Create .gitkeep files to preserve empty directories
    touch "$BUILD_DIR/logs/.gitkeep"
    touch "$BUILD_DIR/data/cache/.gitkeep"
    touch "$BUILD_DIR/data/sessions/.gitkeep"
    
    success "Directories created"
}

# Set production file permissions
set_production_permissions() {
    log "Setting production file permissions..."
    
    # Set directory permissions
    find "$BUILD_DIR" -type d -exec chmod 755 {} \;
    
    # Set file permissions
    find "$BUILD_DIR" -type f -exec chmod 644 {} \;
    
    # Make scripts executable
    chmod +x "$BUILD_DIR/bin/"*.sh
    
    # Protect sensitive configuration templates
    chmod 600 "$BUILD_DIR/config/autoload/"*.dist
    chmod 600 "$BUILD_DIR/.env.dist"
    
    success "Production permissions set"
}

# Create deployment package
create_package() {
    log "Creating deployment package..."
    
    local package_file="${PACKAGE_NAME}_${VERSION}.tar.gz"
    
    # Create tarball
    tar -czf "$package_file" -C "$BUILD_DIR" .
    
    # Create checksum
    sha256sum "$package_file" > "${package_file}.sha256"
    
    success "Package created: $package_file"
    success "Checksum created: ${package_file}.sha256"
    
    # Display package info
    echo ""
    echo "=== Package Information ==="
    echo "File: $package_file"
    echo "Size: $(du -h "$package_file" | cut -f1)"
    echo "SHA256: $(cat "${package_file}.sha256" | cut -d' ' -f1)"
    echo "=========================="
}

# Create deployment instructions
create_deployment_instructions() {
    log "Creating deployment instructions..."
    
    cat > "DEPLOYMENT_INSTRUCTIONS.txt" << EOF
HDM Boot Protocol - Production Deployment Instructions
=====================================================

Package: ${PACKAGE_NAME}_${VERSION}.tar.gz
Created: $(date)

Prerequisites:
- PHP 8.1+ with required extensions
- Web server (Nginx/Apache)
- Database server (MySQL/PostgreSQL)
- Redis server (for sessions)

Deployment Steps:

1. Extract package to web directory:
   tar -xzf ${PACKAGE_NAME}_${VERSION}.tar.gz -C /var/www/mezzio-app

2. Copy and configure environment:
   cp .env.dist .env
   # Edit .env with your settings

3. Copy and configure database:
   cp config/autoload/database.local.php.dist config/autoload/database.local.php
   # Edit database.local.php with your database settings

4. Copy and configure sessions:
   cp config/autoload/session.local.php.dist config/autoload/session.local.php
   # Edit session.local.php with your session settings

5. Set proper permissions:
   chown -R www-data:www-data /var/www/mezzio-app
   chmod 775 /var/www/mezzio-app/data /var/www/mezzio-app/logs

6. Run deployment script:
   sudo /var/www/mezzio-app/bin/deploy.sh

7. Configure web server to point to /var/www/mezzio-app/public

8. Set up monitoring:
   # Add to crontab:
   */5 * * * * /var/www/mezzio-app/bin/monitor.sh

Health Check:
- URL: http://your-domain.com/health.php
- Should return JSON with status "ok"

Support:
- Documentation: docs/
- Health monitoring: bin/monitor.sh
- Deployment: bin/deploy.sh

EOF

    success "Deployment instructions created: DEPLOYMENT_INSTRUCTIONS.txt"
}

# Restore development environment
restore_development() {
    log "Restoring development environment..."
    
    # Restore composer.lock if it was backed up
    if [ -f "composer.lock.backup" ]; then
        mv composer.lock.backup composer.lock
    fi
    
    # Reinstall development dependencies
    composer install
    
    success "Development environment restored"
}

# Validate build
validate_build() {
    log "Validating build..."
    
    local errors=0
    
    # Check required files
    local required_files=(
        "public/index.php"
        "config/config.php"
        "vendor/autoload.php"
        "bin/deploy.sh"
        "bin/monitor.sh"
        "public/health.php"
    )
    
    for file in "${required_files[@]}"; do
        if [ ! -f "$BUILD_DIR/$file" ]; then
            error "Required file missing: $file"
            errors=$((errors + 1))
        fi
    done
    
    # Check required directories
    local required_dirs=(
        "config/autoload"
        "modules"
        "src"
        "vendor"
        "logs"
        "data"
    )
    
    for dir in "${required_dirs[@]}"; do
        if [ ! -d "$BUILD_DIR/$dir" ]; then
            error "Required directory missing: $dir"
            errors=$((errors + 1))
        fi
    done
    
    if [ $errors -eq 0 ]; then
        success "Build validation passed"
    else
        error "Build validation failed with $errors errors"
        exit 1
    fi
}

# Main build function
main() {
    log "Starting HDM Boot Protocol production build..."
    
    clean_build
    install_production_dependencies
    copy_application_files
    create_production_configs
    optimize_autoloader
    create_directories
    set_production_permissions
    validate_build
    create_package
    create_deployment_instructions
    restore_development
    
    success "Production build completed successfully!"
    
    echo ""
    echo "=== Build Summary ==="
    echo "Build directory: $BUILD_DIR"
    echo "Package: ${PACKAGE_NAME}_${VERSION}.tar.gz"
    echo "Instructions: DEPLOYMENT_INSTRUCTIONS.txt"
    echo "==================="
    echo ""
    echo "Ready for production deployment!"
}

# Run main function
main "$@"
