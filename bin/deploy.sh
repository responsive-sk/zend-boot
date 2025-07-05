#!/bin/bash

# HDM Boot Protocol - Production Deployment Script
# 
# This script handles the deployment of the Mezzio application to production
# with proper backup, dependency installation, and service management.

set -e

# Configuration
APP_DIR="${APP_DIR:-/var/www/mezzio-app}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/mezzio-app}"
WEB_USER="${WEB_USER:-www-data}"
WEB_GROUP="${WEB_GROUP:-www-data}"
PHP_VERSION="${PHP_VERSION:-8.1}"
DATE=$(date +%Y%m%d_%H%M%S)

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

# Check if running as root or with sudo
check_permissions() {
    if [[ $EUID -ne 0 ]]; then
        error "This script must be run as root or with sudo"
        exit 1
    fi
}

# Create necessary directories
create_directories() {
    log "Creating necessary directories..."
    
    mkdir -p "$BACKUP_DIR"
    mkdir -p "$APP_DIR/logs"
    mkdir -p "$APP_DIR/data/cache"
    mkdir -p "$APP_DIR/data/sessions"
    
    success "Directories created"
}

# Create backup
create_backup() {
    log "Creating backup..."
    
    if [ -d "$APP_DIR" ]; then
        tar -czf "$BACKUP_DIR/pre_deploy_$DATE.tar.gz" \
            --exclude='vendor' \
            --exclude='data/cache' \
            --exclude='logs/*.log' \
            "$APP_DIR" 2>/dev/null || true
        
        success "Backup created: pre_deploy_$DATE.tar.gz"
    else
        warning "Application directory does not exist, skipping backup"
    fi
}

# Update application code
update_code() {
    log "Updating application code..."
    
    cd "$APP_DIR"
    
    # If git repository exists, pull latest changes
    if [ -d ".git" ]; then
        git pull origin main
        success "Code updated from git"
    else
        warning "Not a git repository, skipping code update"
    fi
}

# Install dependencies
install_dependencies() {
    log "Installing dependencies..."
    
    cd "$APP_DIR"
    
    # Install composer dependencies for production
    composer install --no-dev --optimize-autoloader --no-interaction
    
    success "Dependencies installed"
}

# Run database migrations
run_migrations() {
    log "Running database migrations..."
    
    cd "$APP_DIR"
    
    # Check if migration script exists
    if [ -f "bin/migrate.php" ]; then
        php bin/migrate.php
        success "Database migrations completed"
    else
        warning "Migration script not found, skipping migrations"
    fi
}

# Clear cache
clear_cache() {
    log "Clearing application cache..."
    
    rm -rf "$APP_DIR/data/cache/*" 2>/dev/null || true
    rm -rf "$APP_DIR/data/sessions/*" 2>/dev/null || true
    
    success "Cache cleared"
}

# Set proper permissions
set_permissions() {
    log "Setting file permissions..."
    
    # Set ownership
    chown -R "$WEB_USER:$WEB_GROUP" "$APP_DIR"
    
    # Set directory permissions
    find "$APP_DIR" -type d -exec chmod 755 {} \;
    
    # Set file permissions
    find "$APP_DIR" -type f -exec chmod 644 {} \;
    
    # Make scripts executable
    chmod +x "$APP_DIR/bin/"*.sh 2>/dev/null || true
    
    # Writable directories
    chmod 775 "$APP_DIR/data"
    chmod 775 "$APP_DIR/logs"
    chmod 775 "$APP_DIR/data/cache"
    chmod 775 "$APP_DIR/data/sessions"
    
    # Protect sensitive files
    chmod 600 "$APP_DIR/config/autoload/"*.local.php 2>/dev/null || true
    chmod 600 "$APP_DIR/.env" 2>/dev/null || true
    
    success "Permissions set"
}

# Restart services
restart_services() {
    log "Restarting services..."
    
    # Restart PHP-FPM
    if systemctl is-active --quiet "php$PHP_VERSION-fpm"; then
        systemctl reload "php$PHP_VERSION-fpm"
        success "PHP-FPM reloaded"
    else
        warning "PHP-FPM service not running"
    fi
    
    # Restart web server
    if systemctl is-active --quiet nginx; then
        systemctl reload nginx
        success "Nginx reloaded"
    elif systemctl is-active --quiet apache2; then
        systemctl reload apache2
        success "Apache reloaded"
    else
        warning "No web server found to reload"
    fi
    
    # Restart Redis if used for sessions
    if systemctl is-active --quiet redis-server; then
        systemctl restart redis-server
        success "Redis restarted"
    fi
}

# Health check
health_check() {
    log "Performing health check..."
    
    # Wait a moment for services to start
    sleep 5
    
    # Check if health endpoint exists and responds
    if curl -f -s "http://localhost/health.php" > /dev/null 2>&1; then
        success "Health check passed"
    else
        warning "Health check failed or endpoint not available"
    fi
}

# Cleanup old backups
cleanup_backups() {
    log "Cleaning up old backups..."
    
    # Keep only last 7 days of backups
    find "$BACKUP_DIR" -name "pre_deploy_*.tar.gz" -mtime +7 -delete 2>/dev/null || true
    
    success "Old backups cleaned up"
}

# Main deployment function
main() {
    log "Starting HDM Boot Protocol deployment..."
    
    check_permissions
    create_directories
    create_backup
    update_code
    install_dependencies
    run_migrations
    clear_cache
    set_permissions
    restart_services
    health_check
    cleanup_backups
    
    success "Deployment completed successfully!"
    log "Application is now running in production mode"
    
    # Display important information
    echo ""
    echo "=== Deployment Summary ==="
    echo "Date: $(date)"
    echo "Backup: $BACKUP_DIR/pre_deploy_$DATE.tar.gz"
    echo "Application: $APP_DIR"
    echo "Web User: $WEB_USER:$WEB_GROUP"
    echo "=========================="
}

# Run main function
main "$@"
