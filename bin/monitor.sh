#!/bin/bash

# HDM Boot Protocol - System Monitoring Script
# 
# This script monitors the application health, system resources,
# and logs for any issues that need attention.

set -e

# Configuration
APP_DIR="${APP_DIR:-/var/www/mezzio-app}"
LOG_DIR="${LOG_DIR:-$APP_DIR/logs}"
HEALTH_URL="${HEALTH_URL:-http://localhost/health.php}"
ALERT_EMAIL="${ALERT_EMAIL:-admin@your-domain.com}"
DISK_WARNING_THRESHOLD="${DISK_WARNING_THRESHOLD:-80}"
DISK_CRITICAL_THRESHOLD="${DISK_CRITICAL_THRESHOLD:-90}"
MEMORY_WARNING_THRESHOLD="${MEMORY_WARNING_THRESHOLD:-80}"
MEMORY_CRITICAL_THRESHOLD="${MEMORY_CRITICAL_THRESHOLD:-90}"

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
    echo -e "${GREEN}[OK]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Send alert email (if configured)
send_alert() {
    local subject="$1"
    local message="$2"
    
    if command -v mail >/dev/null 2>&1 && [ -n "$ALERT_EMAIL" ]; then
        echo "$message" | mail -s "$subject" "$ALERT_EMAIL"
        log "Alert sent to $ALERT_EMAIL"
    fi
}

# Check application health
check_application_health() {
    log "Checking application health..."
    
    if curl -f -s "$HEALTH_URL" >/dev/null 2>&1; then
        local health_response=$(curl -s "$HEALTH_URL")
        local status=$(echo "$health_response" | grep -o '"status":"[^"]*"' | cut -d'"' -f4)
        
        case "$status" in
            "ok")
                success "Application health check passed"
                ;;
            "warning")
                warning "Application health check has warnings"
                echo "$health_response" | jq '.' 2>/dev/null || echo "$health_response"
                ;;
            "error")
                error "Application health check failed"
                echo "$health_response" | jq '.' 2>/dev/null || echo "$health_response"
                send_alert "Application Health Check Failed" "$health_response"
                ;;
            *)
                error "Unknown health status: $status"
                ;;
        esac
    else
        error "Application health check endpoint not accessible"
        send_alert "Application Health Check Endpoint Down" "Cannot access $HEALTH_URL"
    fi
}

# Check disk space
check_disk_space() {
    log "Checking disk space..."
    
    df -h | while read filesystem size used avail percent mountpoint; do
        # Skip header line
        if [[ "$filesystem" == "Filesystem" ]]; then
            continue
        fi
        
        # Extract percentage number
        usage=$(echo "$percent" | sed 's/%//')
        
        if [[ "$usage" =~ ^[0-9]+$ ]]; then
            if [ "$usage" -ge "$DISK_CRITICAL_THRESHOLD" ]; then
                error "Disk space critical on $mountpoint: $percent used"
                send_alert "Disk Space Critical" "Disk usage on $mountpoint is $percent"
            elif [ "$usage" -ge "$DISK_WARNING_THRESHOLD" ]; then
                warning "Disk space warning on $mountpoint: $percent used"
            else
                success "Disk space OK on $mountpoint: $percent used"
            fi
        fi
    done
}

# Check memory usage
check_memory_usage() {
    log "Checking memory usage..."
    
    local memory_info=$(free | grep '^Mem:')
    local total=$(echo "$memory_info" | awk '{print $2}')
    local used=$(echo "$memory_info" | awk '{print $3}')
    local usage_percent=$((used * 100 / total))
    
    if [ "$usage_percent" -ge "$MEMORY_CRITICAL_THRESHOLD" ]; then
        error "Memory usage critical: ${usage_percent}%"
        send_alert "Memory Usage Critical" "Memory usage is ${usage_percent}%"
    elif [ "$usage_percent" -ge "$MEMORY_WARNING_THRESHOLD" ]; then
        warning "Memory usage high: ${usage_percent}%"
    else
        success "Memory usage OK: ${usage_percent}%"
    fi
}

# Check log files for errors
check_log_errors() {
    log "Checking log files for errors..."
    
    local error_count=0
    
    # Check application logs
    if [ -f "$LOG_DIR/application.log" ]; then
        local app_errors=$(tail -n 100 "$LOG_DIR/application.log" | grep -i "error\|critical\|emergency" | wc -l)
        if [ "$app_errors" -gt 0 ]; then
            warning "Found $app_errors error(s) in application log"
            error_count=$((error_count + app_errors))
        fi
    fi
    
    # Check error logs
    if [ -f "$LOG_DIR/error.log" ]; then
        local error_log_errors=$(tail -n 100 "$LOG_DIR/error.log" | wc -l)
        if [ "$error_log_errors" -gt 0 ]; then
            warning "Found $error_log_errors error(s) in error log"
            error_count=$((error_count + error_log_errors))
        fi
    fi
    
    # Check PHP error log
    local php_error_log=$(php -r "echo ini_get('error_log');")
    if [ -f "$php_error_log" ]; then
        local php_errors=$(tail -n 100 "$php_error_log" | grep "$(date '+%d-%b-%Y')" | wc -l)
        if [ "$php_errors" -gt 0 ]; then
            warning "Found $php_errors PHP error(s) today"
            error_count=$((error_count + php_errors))
        fi
    fi
    
    if [ "$error_count" -eq 0 ]; then
        success "No recent errors found in logs"
    else
        warning "Total errors found: $error_count"
        if [ "$error_count" -gt 10 ]; then
            send_alert "High Error Count" "Found $error_count errors in application logs"
        fi
    fi
}

# Check service status
check_services() {
    log "Checking service status..."
    
    local services=("nginx" "apache2" "php8.1-fpm" "php8.2-fpm" "redis-server" "mysql" "mariadb")
    
    for service in "${services[@]}"; do
        if systemctl list-unit-files | grep -q "^$service.service"; then
            if systemctl is-active --quiet "$service"; then
                success "$service is running"
            else
                error "$service is not running"
                send_alert "Service Down" "$service service is not running"
            fi
        fi
    done
}

# Check SSL certificate expiration
check_ssl_certificate() {
    local domain="$1"
    
    if [ -n "$domain" ]; then
        log "Checking SSL certificate for $domain..."
        
        local expiry_date=$(echo | openssl s_client -servername "$domain" -connect "$domain:443" 2>/dev/null | openssl x509 -noout -dates | grep notAfter | cut -d= -f2)
        
        if [ -n "$expiry_date" ]; then
            local expiry_timestamp=$(date -d "$expiry_date" +%s)
            local current_timestamp=$(date +%s)
            local days_until_expiry=$(( (expiry_timestamp - current_timestamp) / 86400 ))
            
            if [ "$days_until_expiry" -le 7 ]; then
                error "SSL certificate expires in $days_until_expiry days"
                send_alert "SSL Certificate Expiring" "SSL certificate for $domain expires in $days_until_expiry days"
            elif [ "$days_until_expiry" -le 30 ]; then
                warning "SSL certificate expires in $days_until_expiry days"
            else
                success "SSL certificate valid for $days_until_expiry days"
            fi
        else
            warning "Could not check SSL certificate for $domain"
        fi
    fi
}

# Check database connectivity
check_database() {
    log "Checking database connectivity..."
    
    if [ -f "$APP_DIR/config/autoload/database.local.php" ]; then
        # Try to connect using the health check endpoint
        local health_response=$(curl -s "$HEALTH_URL" 2>/dev/null)
        local db_status=$(echo "$health_response" | grep -o '"database":"[^"]*"' | cut -d'"' -f4)
        
        case "$db_status" in
            "ok")
                success "Database connectivity OK"
                ;;
            "error")
                error "Database connectivity failed"
                send_alert "Database Connection Failed" "Cannot connect to database"
                ;;
            *)
                warning "Database status unknown"
                ;;
        esac
    else
        warning "Database configuration not found"
    fi
}

# Generate monitoring report
generate_report() {
    log "Generating monitoring report..."
    
    local report_file="$LOG_DIR/monitoring_$(date +%Y%m%d_%H%M%S).log"
    
    {
        echo "=== HDM Boot Protocol Monitoring Report ==="
        echo "Date: $(date)"
        echo "Host: $(hostname)"
        echo ""
        
        echo "=== System Information ==="
        uname -a
        echo ""
        
        echo "=== Disk Usage ==="
        df -h
        echo ""
        
        echo "=== Memory Usage ==="
        free -h
        echo ""
        
        echo "=== Load Average ==="
        uptime
        echo ""
        
        echo "=== Process Count ==="
        ps aux | wc -l
        echo ""
        
        echo "=== Network Connections ==="
        netstat -tuln | grep LISTEN | wc -l
        echo ""
        
        echo "=== Recent Log Errors ==="
        if [ -f "$LOG_DIR/error.log" ]; then
            tail -n 20 "$LOG_DIR/error.log"
        else
            echo "No error log found"
        fi
        
    } > "$report_file"
    
    success "Monitoring report saved to $report_file"
}

# Main monitoring function
main() {
    log "Starting HDM Boot Protocol monitoring..."
    
    check_application_health
    check_disk_space
    check_memory_usage
    check_log_errors
    check_services
    check_database
    
    # Check SSL certificate if domain is provided
    if [ -n "$1" ]; then
        check_ssl_certificate "$1"
    fi
    
    # Generate report if requested
    if [ "$2" = "--report" ]; then
        generate_report
    fi
    
    success "Monitoring completed"
}

# Run main function with arguments
main "$@"
