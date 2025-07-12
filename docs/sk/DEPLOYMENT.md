# 🚀 Deployment Návod

Kompletný návod na nasadenie Mezzio Minimal aplikácie do produkcie.

## 📋 Prehľad

Tento návod pokrýva deployment od lokálneho buildu až po produkčný server s Apache/Nginx.

##  Production Build

### 1. Vytvorenie Production Buildu

```bash
# Kompletný production build
composer build:production:package

# Výsledok v build/production/ (5.8MB)
ls -la build/production/
```

### 2. Build Optimalizácie

Build proces automaticky:
- **Odstráni development dependencies** (--no-dev)
- **Optimalizuje autoloader** (--optimize-autoloader)
- **Builduje themes s hash** pre cache busting
- **Vyčistí vendor** (docs, tests, examples)
- **Nastaví production permissions** (755/644)
- **Vytvorí build info** s detailmi

### 3. Overenie Buildu

```bash
# Skontroluj veľkosť buildu
du -sh build/production/

# Skontroluj build info
cat build/production/build-info.txt

# Test production buildu lokálne
cd build/production && php -S localhost:8080 -t public/
```

##  Server Requirements

### Minimálne Požiadavky

- **PHP 8.3+** s rozšíreniami: pdo_sqlite, session, openssl, mbstring, json
- **Apache 2.4+** alebo **Nginx 1.18+**
- **SSL/TLS certifikát** (Let's Encrypt odporúčané)
- **Disk space**: 50MB+ (pre aplikáciu + logy)
- **Memory**: 128MB+ PHP memory limit

### Odporúčané Rozšírenia

```bash
# Skontroluj PHP rozšírenia na serveri
php -m | grep -E "(pdo|sqlite|session|openssl|mbstring|json|opcache|gzip)"
```

##  Apache Deployment

### 1. Virtual Host Konfigurácia

```apache
# /etc/apache2/sites-available/mezzio-app.conf
<VirtualHost *:80>
    ServerName example.com
    DocumentRoot /var/www/html/public
    
    # Redirect to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName example.com
    DocumentRoot /var/www/html/public
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    
    # Security Headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/mezzio_error.log
    CustomLog ${APACHE_LOG_DIR}/mezzio_access.log combined
    
    # Directory Configuration
    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 2. Upload na Server

```bash
# Upload production buildu
scp -r build/production/* user@server:/var/www/html/

# Alebo pomocou rsync
rsync -avz --delete build/production/ user@server:/var/www/html/
```

### 3. Nastavenie Permissions

```bash
# Na serveri nastav správne permissions
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo chmod -R 644 /var/www/html/config /var/www/html/src

# Writable adresáre
sudo chmod -R 775 /var/www/html/data
sudo chmod -R 775 /var/www/html/var
```

##  Docker Deployment

### 1. Dockerfile

```dockerfile
FROM php:8.3-apache

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite

# Enable Apache modules
RUN a2enmod rewrite ssl headers

# Copy application
COPY build/production/ /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Expose port
EXPOSE 80 443
```

### 2. Docker Compose

```yaml
# docker-compose.yml
version: '3.8'
services:
  web:
    build: .
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./data:/var/www/html/data
      - ./var/logs:/var/www/html/var/logs
    environment:
      - APP_ENV=production
      - DEBUG=false
```

### 3. Build a Deploy

```bash
# Build Docker image
docker build -t mezzio-app .

# Spusti container
docker-compose up -d

# Skontroluj status
docker-compose ps
```

##  Production Security Setup

### 1. Environment Variables

```bash
# /var/www/html/.env.local
APP_ENV=production
DEBUG=false
LOG_LEVEL=error
SESSION_SECURE=true
DB_ENCRYPTION_KEY=your-secret-key-here
```

### 2. Database Migration

```bash
# Na serveri spusti migrácie
cd /var/www/html
php bin/migrate.php
```

### 3. SSL/TLS Setup

```bash
# Let's Encrypt certifikát
sudo certbot --apache -d example.com

# Automatické obnovenie
sudo crontab -e
0 12 * * * /usr/bin/certbot renew --quiet
```

##  Monitoring & Maintenance

### 1. Log Monitoring

```bash
# Sleduj error logy
sudo tail -f /var/log/apache2/mezzio_error.log

# Sleduj aplikačné logy
tail -f /var/www/html/var/logs/app.log
```

### 2. Health Checks

```bash
# Vytvor health check script
#!/bin/bash
# /usr/local/bin/health-check.sh

curl -f http://localhost/health.php || exit 1
php /var/www/html/bin/health-check.php || exit 1
```

### 3. Backup Strategy

```bash
# Denný backup databáz
#!/bin/bash
# /usr/local/bin/backup-db.sh

DATE=$(date +%Y%m%d_%H%M%S)
tar -czf /backups/db_backup_$DATE.tar.gz /var/www/html/data/
find /backups -name "db_backup_*.tar.gz" -mtime +7 -delete
```

### 4. Cron Jobs

```bash
# /etc/crontab
# Denný backup o 2:00
0 2 * * * root /usr/local/bin/backup-db.sh

# Health check každých 5 minút
*/5 * * * * root /usr/local/bin/health-check.sh

# Log rotation týždenne
0 0 * * 0 root logrotate /etc/logrotate.d/mezzio-app
```

##  Update Process

### 1. Príprava Nového Buildu

```bash
# Lokálne vytvor nový build
composer build:production:package

# Vytvor backup súčasnej verzie
ssh user@server "tar -czf /backups/app_backup_$(date +%Y%m%d).tar.gz /var/www/html"
```

### 2. Deploy Novej Verzie

```bash
# Upload novej verzie
rsync -avz --delete build/production/ user@server:/var/www/html/

# Spusti migrácie ak potrebné
ssh user@server "cd /var/www/html && php bin/migrate.php"
```

### 3. Rollback Plan

```bash
# V prípade problémov rollback
ssh user@server "cd /backups && tar -xzf app_backup_YYYYMMDD.tar.gz -C /"
```

##  Troubleshooting

### Časté Problémy

**500 Internal Server Error**
```bash
# Skontroluj Apache error log
sudo tail -f /var/log/apache2/error.log

# Skontroluj permissions
ls -la /var/www/html/
```

**Database Connection Error**
```bash
# Skontroluj database permissions
ls -la /var/www/html/data/
sudo chmod 775 /var/www/html/data/
```

**Assets Not Loading**
```bash
# Skontroluj .htaccess
cat /var/www/html/public/.htaccess

# Rebuild themes
cd themes/bootstrap && pnpm run build:prod
```

---

**Ďalšie informácie:**
- [BEZPECNOST.md](BEZPECNOST.md) - Production security
- [../APACHE_GUIDE.md](../APACHE_GUIDE.md) - Detailná Apache konfigurácia
- [RIESENIE_PROBLEMOV.md](RIESENIE_PROBLEMOV.md) - Troubleshooting
- [../MAINTENANCE.md](../MAINTENANCE.md) - Maintenance procedures
