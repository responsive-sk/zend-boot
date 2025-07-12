# 🐛 Riešenie Problémov

Návod na riešenie častých problémov s Mezzio Minimal aplikáciou.

##  Časté Problémy

### 1. 500 Internal Server Error

**Príznaky:** Biela stránka alebo "Internal Server Error"

**Riešenie:**
```bash
# 1. Skontroluj PHP error log
tail -f /var/log/php_errors.log
# Alebo
tail -f var/logs/app.log

# 2. Zapni error reporting (development)
echo "display_errors = On" >> php.ini
echo "error_reporting = E_ALL" >> php.ini

# 3. Skontroluj permissions
chmod -R 755 data/ var/ public/themes/
chmod -R 644 config/ src/

# 4. Skontroluj .htaccess
cat public/.htaccess
```

### 2. Database Connection Error

**Príznaky:** "Database not found" alebo "PDO connection failed"

**Riešenie:**
```bash
# 1. Spusti migrácie
php bin/migrate.php

# 2. Skontroluj database permissions
ls -la data/
chmod 775 data/
chmod 664 data/*.db

# 3. Skontroluj database konfiguráciu
cat config/autoload/database.global.php

# 4. Test database connection
php -r "
$pdo = new PDO('sqlite:data/user.db');
echo 'Database connection OK';
"
```

### 3. Assets Not Loading (CSS/JS)

**Príznaky:** Stránka bez štýlov, JavaScript nefunguje

**Riešenie:**
```bash
# 1. Rebuild themes
cd themes/bootstrap
pnpm install
pnpm run build:prod

cd themes/main
pnpm install
pnpm run build:prod

# 2. Skontroluj manifest súbory
cat public/themes/bootstrap/assets/manifest.json
cat public/themes/main/assets/manifest.json

# 3. Skontroluj permissions
chmod -R 755 public/themes/

# 4. Vyčisti cache
composer clean:build
composer build:themes:prod
```

### 4. Session Problems

**Príznaky:** Neustále odhlasovanie, session data sa nestráca

**Riešenie:**
```bash
# 1. Skontroluj session adresár
ls -la var/sessions/
chmod 775 var/sessions/

# 2. Skontroluj session konfiguráciu
cat config/autoload/session.global.php

# 3. Vyčisti sessions
rm -rf var/sessions/*

# 4. Restart web server
sudo systemctl restart apache2
```

### 5. CSRF Token Errors

**Príznaky:** "Invalid CSRF token" pri odosielaní formulárov

**Riešenie:**
```bash
# 1. Skontroluj session storage
ls -la var/sessions/

# 2. Vyčisti browser cache a cookies
# V browseri: Ctrl+Shift+Delete

# 3. Skontroluj CSRF middleware
grep -r "CsrfMiddleware" config/

# 4. Debug CSRF token
# V template pridaj:
echo "CSRF Token: " . $csrfToken;
```

### 6. Permission Denied Errors

**Príznaky:** "Permission denied" pri čítaní/zápise súborov

**Riešenie:**
```bash
# 1. Nastav správne ownership
sudo chown -R www-data:www-data /var/www/html
# Alebo pre development:
sudo chown -R $USER:$USER .

# 2. Nastav permissions
chmod -R 755 .
chmod -R 775 data/ var/ public/themes/
chmod -R 644 config/ src/

# 3. Skontroluj SELinux (ak je aktívny)
sudo setsebool -P httpd_can_network_connect 1
sudo setsebool -P httpd_unified 1
```

##  Development Problémy

### 1. Composer Install Fails

**Riešenie:**
```bash
# 1. Vyčisti composer cache
composer clear-cache

# 2. Update composer
composer self-update

# 3. Install s verbose output
composer install -vvv

# 4. Skontroluj PHP verziu
php -v  # Musí byť 8.3+
```

### 2. Node.js/pnpm Problémy

**Riešenie:**
```bash
# 1. Skontroluj Node.js verziu
node -v  # Musí byť 18+
pnpm -v

# 2. Vyčisti node_modules
rm -rf themes/*/node_modules
rm -rf themes/*/package-lock.json

# 3. Reinstall dependencies
cd themes/bootstrap && pnpm install
cd themes/main && pnpm install

# 4. Rebuild themes
pnpm run build:prod
```

### 3. Theme Build Errors

**Riešenie:**
```bash
# 1. Skontroluj Vite konfiguráciu
cat themes/bootstrap/vite.config.js
cat themes/main/vite.config.js

# 2. Debug build process
cd themes/bootstrap
pnpm run build --debug

# 3. Skontroluj source súbory
ls -la src/

# 4. Vyčisti build cache
rm -rf dist/ .vite/
```

##  Production Problémy

### 1. Apache Configuration Issues

**Riešenie:**
```bash
# 1. Test Apache konfigurácie
sudo apache2ctl configtest

# 2. Skontroluj virtual host
cat /etc/apache2/sites-available/mezzio-app.conf

# 3. Enable required modules
sudo a2enmod rewrite ssl headers

# 4. Restart Apache
sudo systemctl restart apache2
```

### 2. SSL/HTTPS Problémy

**Riešenie:**
```bash
# 1. Skontroluj SSL certifikát
openssl x509 -in /path/to/certificate.crt -text -noout

# 2. Test SSL konfigurácie
sudo apache2ctl -S

# 3. Obnovenie Let's Encrypt
sudo certbot renew --dry-run

# 4. Skontroluj SSL logs
sudo tail -f /var/log/apache2/ssl_error.log
```

### 3. Performance Problémy

**Riešenie:**
```bash
# 1. Enable OPcache
echo "opcache.enable=1" >> /etc/php/8.3/apache2/php.ini

# 2. Skontroluj memory limit
php -i | grep memory_limit

# 3. Enable Gzip compression
sudo a2enmod deflate

# 4. Optimalizuj database
sqlite3 data/user.db "VACUUM;"
```

##  Debugging Tools

### 1. PHP Error Logging

```php
// V development.config.php
return [
    'debug' => true,
    'config_cache_enabled' => false,
    'error_handler' => [
        'display' => true,
        'log' => true,
    ],
];
```

### 2. Database Debugging

```bash
# SQLite command line
sqlite3 data/user.db
.tables
.schema users
SELECT * FROM users;
```

### 3. Asset Debugging

```bash
# Skontroluj manifest súbory
cat public/themes/bootstrap/assets/manifest.json | jq .

# Test asset URLs
curl -I http://localhost:8080/themes/bootstrap/assets/main-hash.css
```

### 4. Session Debugging

```php
// V handleri
var_dump($_SESSION);
var_dump(session_id());
var_dump(session_status());
```

##  Health Checks

### 1. Aplikačný Health Check

```bash
# Spusti built-in health check
php bin/health-check.php

# Alebo cez web
curl http://localhost:8080/health.php
```

### 2. System Health Check

```bash
# Disk space
df -h

# Memory usage
free -h

# PHP processes
ps aux | grep php

# Apache status
sudo systemctl status apache2
```

### 3. Database Health

```bash
# Database integrity check
sqlite3 data/user.db "PRAGMA integrity_check;"

# Database size
ls -lh data/*.db
```

##  Emergency Procedures

### 1. Rollback Deployment

```bash
# Restore from backup
cd /backups
tar -xzf app_backup_YYYYMMDD.tar.gz -C /var/www/html/

# Restart services
sudo systemctl restart apache2
```

### 2. Database Recovery

```bash
# Restore database from backup
cp /backups/user.db.backup data/user.db
chmod 664 data/user.db

# Re-run migrations
php bin/migrate.php
```

### 3. Emergency Maintenance Mode

```bash
# Vytvor maintenance page
echo "Site under maintenance" > public/maintenance.html

# Redirect všetky requesty
# V .htaccess pridaj:
RewriteEngine On
RewriteCond %{REQUEST_URI} !^/maintenance.html$
RewriteRule ^(.*)$ /maintenance.html [R=503,L]
```

##  Kontakt a Podpora

### Logy na Sledovanie

```bash
# Aplikačné logy
tail -f var/logs/app.log

# Apache logy
sudo tail -f /var/log/apache2/error.log

# PHP logy
sudo tail -f /var/log/php_errors.log

# System logy
sudo journalctl -f -u apache2
```

### Užitočné Príkazy

```bash
# Kompletný restart
sudo systemctl restart apache2 php8.3-fpm

# Vyčistenie cache
rm -rf var/cache/* var/sessions/*

# Permissions reset
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod -R 775 data/ var/
```

---

**Ak problém pretrváva:**
1. Skontroluj [BEZPECNOST.md](BEZPECNOST.md) pre security issues
2. Pozri [DEPLOYMENT.md](DEPLOYMENT.md) pre production setup
3. Konzultuj [/docs/en/MAINTENANCE.md](../MAINTENANCE.md) pre advanced troubleshooting
4. Vytvor issue s detailnými logmi a error messages
