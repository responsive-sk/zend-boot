#!/bin/bash

# HDM Boot Protocol - Production Build Script
# 
# This script prepares the application for production deployment
# by optimizing dependencies, clearing development files, and
# creating a production-ready package.

set -e

# Configuration
BUILD_TARGET="${1:-production}"  # production, shared-hosting, shared-hosting-minimal
BUILD_DIR="${BUILD_DIR:-./build}"
PACKAGE_NAME="${PACKAGE_NAME:-mezzio-hdm-boot-protocol}"
VERSION="${VERSION:-$(date +%Y%m%d_%H%M%S)}"

# Target-specific configurations
case "$BUILD_TARGET" in
    "shared-hosting-minimal")
        BUILD_DIR="${BUILD_DIR}/shared-hosting-minimal"
        PACKAGE_NAME="${PACKAGE_NAME}-shared-hosting-minimal"
        ;;
    "production"|*)
        BUILD_DIR="${BUILD_DIR}/production"
        PACKAGE_NAME="${PACKAGE_NAME}-production"
        ;;
esac
# Define exclude patterns based on build target
get_exclude_patterns() {
    local target="$1"

    # Base patterns for all builds
    local base_patterns=(
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
    )

    # Additional patterns for shared-hosting-minimal
    local minimal_patterns=(
        "docs"
        "*.md"
        "README*"
        "CHANGELOG*"
        "LICENSE*"
        "backup"
        "coverage-html"
        "coverage.txt"
        ".idea"
        ".phpunit.cache"
        ".vscode"
        "*.backup"
        "*.tar.gz"
        "*.tar.gz.sha256"
        "bin/build-*.sh"
        "bin/test-*.sh"
        "bin/dev-*.sh"
        "bin/rector.sh"
        "bin/phpstan.sh"
        "bin/phpcs.sh"
        "bin/coverage.sh"
        "bin/backup-databases.php"
        "bin/maintenance-db.php"
        "bin/migrate-to-hdm-paths.php"
        "data/*.db"
        "data/backup"
        "themes/*/node_modules"
        "themes/*/package*.json"
        "public/themes/*/assets/dev"
        "var/storage/*.db"
        "clover.xml"
        "rector.php"
        "debug-templates.php"
        "DEPLOYMENT_INSTRUCTIONS.txt"
    )

    case "$target" in
        "shared-hosting-minimal")
            printf '%s\n' "${base_patterns[@]}" "${minimal_patterns[@]}"
            ;;
        "shared-hosting")
            printf '%s\n' "${base_patterns[@]}"
            echo "docs/screenshots"
            echo "*.md"
            ;;
        "production"|*)
            printf '%s\n' "${base_patterns[@]}"
            echo "docs/screenshots"
            echo "*.md"
            ;;
    esac
}

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
    log "Copying application files for $BUILD_TARGET build..."

    # Get exclude patterns for current build target
    local exclude_patterns
    readarray -t exclude_patterns < <(get_exclude_patterns "$BUILD_TARGET")

    # Create rsync exclude pattern
    local exclude_args=""
    for pattern in "${exclude_patterns[@]}"; do
        exclude_args="$exclude_args --exclude=$pattern"
    done

    # Copy files using rsync
    rsync -av $exclude_args ./ "$BUILD_DIR/"

    # Additional cleanup for minimal builds
    if [ "$BUILD_TARGET" = "shared-hosting-minimal" ]; then
        log "Performing additional cleanup for minimal build..."

        # Remove any remaining development files
        find "$BUILD_DIR" -name "*.backup" -delete 2>/dev/null || true
        find "$BUILD_DIR" -name ".DS_Store" -delete 2>/dev/null || true
        find "$BUILD_DIR" -name "Thumbs.db" -delete 2>/dev/null || true

        # Remove empty directories
        find "$BUILD_DIR" -type d -empty -delete 2>/dev/null || true

        log "Optimizing .htaccess for shared hosting compatibility..."

        # Keep original structure but ensure Apache 2.2 compatibility
        # User will set document root to /public/ directory on shared hosting

        success "Minimal build cleanup completed"
    fi

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

# Minimize bin scripts for shared hosting
minimize_bin_scripts() {
    if [ "$BUILD_TARGET" = "shared-hosting-minimal" ]; then
        log "Minimizing bin scripts for shared hosting..."

        # Keep only essential scripts for shared hosting
        local essential_scripts=(
            "deploy.sh"
            "monitor.sh"
            "health-check.php"
            "shared-hosting-cleanup.php"
            "init-all-db.php"
        )

        # Remove all scripts first
        rm -f "$BUILD_DIR/bin/"*

        # Copy back only essential scripts
        for script in "${essential_scripts[@]}"; do
            if [ -f "bin/$script" ]; then
                cp "bin/$script" "$BUILD_DIR/bin/"
                log "Kept essential script: $script"
            fi
        done

        success "Bin scripts minimized for shared hosting"
    fi
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
    
    success "Production permissions set"
}

# Create deployment package
create_package() {
    log "Creating deployment package..."

    local package_file="${PACKAGE_NAME}_${VERSION}.tar.gz"
    local package_path="$BUILD_DIR/../$package_file"

    # Create tarball in build parent directory
    tar -czf "$package_path" -C "$BUILD_DIR" .

    # Create checksum
    sha256sum "$package_path" > "${package_path}.sha256"

    success "Package created: $package_path"
    success "Checksum created: ${package_path}.sha256"

    # Display package info
    echo ""
    echo "=== Package Information ==="
    echo "File: $package_path"
    echo "Size: $(du -h "$package_path" | cut -f1)"
    echo "SHA256: $(cat "${package_path}.sha256" | cut -d' ' -f1)"
    echo "=========================="
}

# Create deployment instructions
create_deployment_instructions() {
    log "Creating deployment instructions..."

    if [ "$BUILD_TARGET" = "shared-hosting-minimal" ]; then
        cat > "$BUILD_DIR/DEPLOYMENT_INSTRUCTIONS.txt" << EOF
HDM Boot Protocol - Shared Hosting Deployment Instructions
=========================================================

Package: ${PACKAGE_NAME}_${VERSION}.tar.gz
Created: $(date)
Build Type: Shared Hosting Minimal

Prerequisites:
- PHP 8.1+ with required extensions
- Shared hosting with Apache
- MySQL database access
- File manager or FTP access

Deployment Steps:

1. Upload and extract package:
   - Upload ${PACKAGE_NAME}_${VERSION}.tar.gz to your hosting account
   - Extract to your desired directory (e.g., /home/username/mark/)

2. Set Document Root:
   - In your hosting control panel, set document root to: /home/username/mark/public/
   - This ensures only the public directory is web-accessible

3. Set Open Base Dir (if available):
   - Restrict PHP to: /home/username/mark/:/tmp/
   - This improves security by limiting file access

4. Configure database:
   cp config/autoload/database.local.php.dist config/autoload/database.local.php
   # Edit database.local.php with your hosting database credentials

5. Configure sessions:
   cp config/autoload/session.local.php.dist config/autoload/session.local.php
   # Edit session settings if needed
SESSION_SECRET=your_random_secret_key_here

Verification:
- Visit your domain to see the application
- Visit /health.php to check system status
- Verify CSS/JS assets load correctly

Troubleshooting:
- Internal Server Error: Check document root points to public/ directory
- Missing assets: Verify public/themes/ directory uploaded correctly
- Database errors: Check .env file configuration
- Apache errors: .htaccess files are Apache 2.2+ compatible

Support:
- Documentation: docs/
- Health check: /health.php
- Minimal bin scripts included for essential operations

EOF
    else
        cat > "$BUILD_DIR/DEPLOYMENT_INSTRUCTIONS.txt" << EOF
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

2. Copy and configure database:
   cp config/autoload/database.local.php.dist config/autoload/database.local.php
   # Edit database.local.php with your database settings

3. Copy and configure sessions:
   cp config/autoload/session.local.php.dist config/autoload/session.local.php
   # Edit session.local.php with your session settings

4. Set proper permissions:
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
    fi

    success "Deployment instructions created: $BUILD_DIR/DEPLOYMENT_INSTRUCTIONS.txt"
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
    log "Starting HDM Boot Protocol $BUILD_TARGET build..."

    clean_build
    install_production_dependencies
    copy_application_files
    create_production_configs
    optimize_autoloader
    minimize_bin_scripts
    create_directories
    set_production_permissions
    validate_build
    create_package
    create_deployment_instructions
    restore_development

    success "$BUILD_TARGET build completed successfully!"

    echo ""
    echo "=== Build Summary ==="
    echo "Build type: $BUILD_TARGET"
    echo "Build directory: $BUILD_DIR"
    echo "Package: ${PACKAGE_NAME}_${VERSION}.tar.gz"
    echo "Instructions: DEPLOYMENT_INSTRUCTIONS.txt"
    echo "==================="
    echo ""
    echo "Ready for deployment!"
}

# Run main function
main "$@"
