# 🚀 Deployment Guide

Kompletný návod na production deployment Mezzio Minimal aplikácie.

## 🏭 Production Deployment

### 1. Príprava Prostredia

#### Požiadavky na Server

- **PHP 8.1+** s extensions:
  - `pdo_sqlite` (development)
  - `pdo_mysql` alebo `pdo_pgsql` (production)
  - `session`
  - `openssl`
  - `mbstring`
  - `json`

- **Web Server**: Apache/Nginx
- **Database**: PostgreSQL/MySQL (production)
- **Session Storage**: Redis (odporúčané)

#### Directory Structure

```
/var/www/mezzio-app/
├── public/              # Document root
├── config/
│   ├── autoload/
│   │   ├── *.global.php
│   │   └── *.local.php  # Production overrides
├── data/               # Writable directory
├── logs/               # Application logs
└── vendor/             # Composer dependencies
```

### 2. Configuration

#### Production Config Override

```php
// config/autoload/database.local.php
return [
    'database' => [
        'user' => [
            'driver' => 'mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'database' => $_ENV['DB_NAME'] ?? 'mezzio_users',
            'username' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASS'] ?? '',
            'charset' => 'utf8mb4',
        ],
    ],
];
```

#### Session Configuration

```php
// config/autoload/session.local.php
return [
    'session' => [
        'cookie_secure' => true,        // HTTPS only
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'ini_settings' => [
            'session.save_handler' => 'redis',
            'session.save_path' => 'tcp://127.0.0.1:6379',
            'session.gc_maxlifetime' => 3600,
        ],
    ],
];
```

#### Environment Variables

```bash
# .env file
DB_HOST=localhost
DB_PORT=3306
DB_NAME=mezzio_users
DB_USER=app_user
DB_PASS=secure_password

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

APP_ENV=production
APP_DEBUG=false
```

### 3. Database Migration

#### MySQL Setup

```sql
-- Create database
CREATE DATABASE mezzio_users CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON mezzio_users.* TO 'app_user'@'localhost';
FLUSH PRIVILEGES;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    roles JSON NOT NULL DEFAULT ('["user"]'),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_active (is_active)
);
```

#### PostgreSQL Setup

```sql
-- Create database
CREATE DATABASE mezzio_users WITH ENCODING 'UTF8';

-- Create user
CREATE USER app_user WITH PASSWORD 'secure_password';
GRANT ALL PRIVILEGES ON DATABASE mezzio_users TO app_user;

-- Users table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    roles JSONB NOT NULL DEFAULT '["user"]',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_active ON users(is_active);
```

### 4. Web Server Configuration

#### Nginx Configuration

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/mezzio-app/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Mezzio routing
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ /(config|data|logs|vendor) {
        deny all;
    }
}

# HTTP to HTTPS redirect
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

#### Apache Configuration

```apache
<VirtualHost *:443>
    ServerName your-domain.com
    DocumentRoot /var/www/mezzio-app/public
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    
    # Security Headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    
    # Mezzio routing
    <Directory /var/www/mezzio-app/public>
        AllowOverride All
        Require all granted
        
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>
    
    # Deny access to sensitive directories
    <DirectoryMatch "/(config|data|logs|vendor)">
        Require all denied
    </DirectoryMatch>
</VirtualHost>

<VirtualHost *:80>
    ServerName your-domain.com
    Redirect permanent / https://your-domain.com/
</VirtualHost>
```

### 5. Security Hardening

#### File Permissions

```bash
# Set proper ownership
chown -R www-data:www-data /var/www/mezzio-app

# Set directory permissions
find /var/www/mezzio-app -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/mezzio-app -type f -exec chmod 644 {} \;

# Writable directories
chmod 775 /var/www/mezzio-app/data
chmod 775 /var/www/mezzio-app/logs

# Protect sensitive files
chmod 600 /var/www/mezzio-app/config/autoload/*.local.php
```

#### PHP Configuration

```ini
; php.ini security settings
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/www/mezzio-app/logs/php_errors.log

; Session security
session.cookie_secure = 1
session.cookie_httponly = 1
session.cookie_samesite = "Strict"
session.use_strict_mode = 1

; Upload limits
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 30
memory_limit = 256M
```

### 6. Monitoring & Logging

#### Application Logging

```php
// config/autoload/logging.local.php
return [
    'dependencies' => [
        'factories' => [
            'logger' => function() {
                $logger = new \Monolog\Logger('app');
                $logger->pushHandler(new \Monolog\Handler\StreamHandler(
                    '/var/www/mezzio-app/logs/application.log',
                    \Monolog\Level::Info
                ));
                return $logger;
            },
        ],
    ],
];
```

#### Log Rotation

```bash
# /etc/logrotate.d/mezzio-app
/var/www/mezzio-app/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload php8.1-fpm
    endscript
}
```

### 7. Performance Optimization

#### OPcache Configuration

```ini
; php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
```

#### Redis Session Storage

```bash
# Install Redis
sudo apt install redis-server

# Configure Redis
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

#### Database Optimization

```sql
-- MySQL optimization
SET GLOBAL innodb_buffer_pool_size = 1G;
SET GLOBAL query_cache_size = 256M;

-- Add indexes for performance
CREATE INDEX idx_users_last_login ON users(last_login_at);
CREATE INDEX idx_users_created ON users(created_at);
```

### 8. Backup Strategy

#### Database Backup

```bash
#!/bin/bash
# backup-db.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/mezzio-app"

# MySQL backup
mysqldump -u app_user -p mezzio_users > $BACKUP_DIR/users_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/users_$DATE.sql

# Keep only last 30 days
find $BACKUP_DIR -name "users_*.sql.gz" -mtime +30 -delete
```

#### Application Backup

```bash
#!/bin/bash
# backup-app.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/mezzio-app"

# Backup application files (excluding vendor)
tar -czf $BACKUP_DIR/app_$DATE.tar.gz \
    --exclude='vendor' \
    --exclude='data/cache' \
    --exclude='logs' \
    /var/www/mezzio-app

# Keep only last 7 days
find $BACKUP_DIR -name "app_*.tar.gz" -mtime +7 -delete
```

### 9. Deployment Script

```bash
#!/bin/bash
# deploy.sh

set -e

APP_DIR="/var/www/mezzio-app"
BACKUP_DIR="/var/backups/mezzio-app"
DATE=$(date +%Y%m%d_%H%M%S)

echo "Starting deployment..."

# Create backup
echo "Creating backup..."
tar -czf $BACKUP_DIR/pre_deploy_$DATE.tar.gz $APP_DIR

# Update code
echo "Updating code..."
cd $APP_DIR
git pull origin main

# Install dependencies
echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Run migrations
echo "Running migrations..."
php bin/migrate.php

# Clear cache
echo "Clearing cache..."
rm -rf data/cache/*

# Set permissions
echo "Setting permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR
chmod 775 $APP_DIR/data $APP_DIR/logs

# Restart services
echo "Restarting services..."
systemctl reload php8.1-fpm
systemctl reload nginx

echo "Deployment completed successfully!"
```

### 10. Health Checks

#### Application Health Check

```php
// public/health.php
<?php
header('Content-Type: application/json');

$health = [
    'status' => 'ok',
    'timestamp' => date('c'),
    'checks' => []
];

// Database check
try {
    $pdo = new PDO('mysql:host=localhost;dbname=mezzio_users', 'app_user', 'password');
    $health['checks']['database'] = 'ok';
} catch (Exception $e) {
    $health['checks']['database'] = 'error';
    $health['status'] = 'error';
}

// Session check
if (session_start()) {
    $health['checks']['session'] = 'ok';
    session_destroy();
} else {
    $health['checks']['session'] = 'error';
    $health['status'] = 'error';
}

http_response_code($health['status'] === 'ok' ? 200 : 500);
echo json_encode($health);
```

#### Monitoring Script

```bash
#!/bin/bash
# monitor.sh

# Check application health
curl -f http://localhost/health.php || echo "Application health check failed"

# Check disk space
df -h | awk '$5 > 80 {print "Disk space warning: " $0}'

# Check memory usage
free -m | awk 'NR==2{printf "Memory usage: %s/%sMB (%.2f%%)\n", $3,$2,$3*100/$2 }'

# Check log errors
tail -n 100 /var/www/mezzio-app/logs/application.log | grep -i error
```

Táto dokumentácia poskytuje kompletný návod na deployment Mezzio User modulu do production prostredia s dôrazom na bezpečnosť, výkon a monitorovanie.
