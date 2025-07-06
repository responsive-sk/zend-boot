# 🔧 Troubleshooting Guide

Riešenie najčastejších problémov s Mezzio Minimal aplikáciou.

## 🚨 Kritické Problémy

### 500 Internal Server Error

#### Príčiny a riešenia:

**1. Chýbajúce PHP rozšírenia**
```bash
# Skontroluj potrebné rozšírenia
php -m | grep -E "(pdo|sqlite|session|openssl|mbstring|json)"

# Ubuntu/Debian - nainštaluj chýbajúce
sudo apt install php-pdo php-sqlite3 php-mbstring php-json

# CentOS/RHEL
sudo yum install php-pdo php-sqlite3 php-mbstring php-json
```

**2. Permissions problémy**
```bash
# Nastav správne permissions
chmod -R 755 data/
chmod -R 755 public/themes/
chmod 644 public/.htaccess

# Skontroluj ownership
chown -R www-data:www-data /var/www/mezzio-app/
```

**3. Apache .htaccess problémy**
```bash
# Skontroluj Apache error log
tail -f /var/log/apache2/error.log

# Test Apache konfigurácie
apache2ctl configtest

# Skontroluj AllowOverride
# V virtual host musí byť: AllowOverride All
```

**4. Chýbajúce dependencies**
```bash
# Reinstall dependencies
rm -rf vendor/
composer install --no-dev --optimize-autoloader
```

### 404 Not Found pre všetky routes

#### Riešenie:

**1. Apache mod_rewrite**
```bash
# Skontroluj či je mod_rewrite povolený
apache2ctl -M | grep rewrite

# Ak nie, povoľ ho
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**2. .htaccess súbory**
```bash
# Skontroluj existenciu .htaccess
ls -la public/.htaccess

# Ak chýba, skopíruj z repository
cp .htaccess.example public/.htaccess
```

**3. Virtual Host konfigurácia**
```apache
# V Apache virtual host
<Directory "/var/www/mezzio-app/public">
    AllowOverride All  # Nie None!
    Require all granted
</Directory>
```

## 🎨 Theme a Asset Problémy

### Assets sa nenačítavajú (CSS/JS)

#### Diagnostika:
```bash
# Skontroluj či existujú built assets
ls -la public/themes/bootstrap/assets/
ls -la public/themes/main/assets/

# Skontroluj manifest súbory
cat public/themes/bootstrap/assets/manifest.json
cat public/themes/main/assets/manifest.json
```

#### Riešenie:
```bash
# Rebuild themes
cd themes/bootstrap
pnpm install
pnpm run build:prod

cd ../main
pnpm install  
pnpm run build:prod

# Alebo použiť composer script
composer build:themes:prod
```

### Theme switching nefunguje

#### Riešenie:
```bash
# Skontroluj AssetHelper konfiguráciu
grep -r "AssetHelper" config/

# Skontroluj template usage
grep -r "assetHelper" templates/
```

### Chýbajúce hash v asset názvoch

#### Riešenie:
```bash
# Použiť production build
cd themes/bootstrap && pnpm run build:prod
cd themes/main && pnpm run build:prod

# Nie development build (bez hash)
# pnpm run build  # ❌ Bez hash
```

## 🔐 Bezpečnostné Problémy

### CSRF token errors

#### Riešenie:
```bash
# Skontroluj session konfiguráciu
grep -r "session" config/autoload/

# Skontroluj CSRF middleware
grep -r "CsrfMiddleware" config/

# Vyčisti sessions
rm -rf data/sessions/*
```

### Path traversal blokuje legitímne súbory

#### Riešenie:
```php
// Skontroluj PathService konfiguráciu
// V config/autoload/dependencies.global.php

// Pridaj povolené extensions
'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'css', 'js', 'woff', 'woff2'],

// Skontroluj blocked patterns
'blocked_patterns' => ['..', '\\', '<', '>', ':', '"', '|', '?', '*'],
```

### Security headers nefungujú

#### Riešenie:
```bash
# Skontroluj mod_headers
apache2ctl -M | grep headers

# Ak nie je povolený
sudo a2enmod headers
sudo systemctl restart apache2

# Test headers
curl -I https://your-domain.com
```

## 🗄️ Database Problémy

### "Database not found" error

#### Riešenie:
```bash
# Spusti migrácie znovu
php bin/migrate.php

# Skontroluj permissions na data/ adresár
chmod 755 data/
chmod 644 data/*.db

# Skontroluj či existujú databázy
ls -la data/
```

### "Database is locked" error

#### Riešenie:
```bash
# Skontroluj SQLite procesy
lsof data/user.db

# Kill hanging procesy
pkill -f "php.*migrate"

# Restart web server
sudo systemctl restart apache2
```

### Connection refused (PostgreSQL)

#### Riešenie:
```bash
# Skontroluj PostgreSQL status
sudo systemctl status postgresql

# Skontroluj connection string
grep -r "pgsql:" config/

# Test connection
psql -h localhost -U username -d database_name
```

## 🚀 Build a Deployment Problémy

### Build fails s "command not found"

#### Riešenie:
```bash
# Skontroluj Node.js a pnpm
node --version
pnpm --version

# Nainštaluj ak chýbajú
curl -fsSL https://get.pnpm.io/install.sh | sh
```

### Production build je príliš veľký

#### Riešenie:
```bash
# Použiť production build script
composer build:production:package

# Nie development build
# composer build:dev  # ❌ Veľký

# Skontroluj veľkosť
du -sh build/production/
# Očakávaná veľkosť: ~5.8MB
```

### Deployment permissions problémy

#### Riešenie:
```bash
# Nastav správne permissions po uploade
find /var/www/mezzio-app -type d -exec chmod 755 {} \;
find /var/www/mezzio-app -type f -exec chmod 644 {} \;

# Executable pre bin/ súbory
chmod +x /var/www/mezzio-app/bin/*

# Web server ownership
chown -R www-data:www-data /var/www/mezzio-app/
```

## 🔍 Development Problémy

### Composer serve nefunguje

#### Riešenie:
```bash
# Skontroluj port
netstat -tulpn | grep :8080

# Použiť iný port
php -S localhost:8081 -t public/

# Alebo kill process na porte
lsof -ti:8080 | xargs kill -9
```

### Hot reload nefunguje (Vite)

#### Riešenie:
```bash
# Skontroluj Vite dev server
cd themes/bootstrap
pnpm run dev

# Skontroluj port conflicts
netstat -tulpn | grep :5173

# Restart Vite server
pkill -f vite
pnpm run dev
```

### Tests failing

#### Riešenie:
```bash
# Skontroluj PHPUnit konfiguráciu
./vendor/bin/phpunit --version

# Spusti s verbose output
./vendor/bin/phpunit --verbose

# Skontroluj test database
ls -la data/test_*.db
```

## 📊 Performance Problémy

### Pomalé načítavanie stránok

#### Diagnostika:
```bash
# Skontroluj Apache access log
tail -f /var/log/apache2/access.log

# Skontroluj response times
curl -w "@curl-format.txt" -o /dev/null -s "http://localhost:8080/"
```

#### Riešenie:
```bash
# Zapni OPcache
echo "opcache.enable=1" >> /etc/php/8.3/apache2/php.ini

# Zapni gzip compression
sudo a2enmod deflate
sudo systemctl restart apache2

# Skontroluj cache headers
curl -I https://your-domain.com/themes/bootstrap/assets/main-hash.css
```

### Vysoké memory usage

#### Riešenie:
```bash
# Skontroluj PHP memory limit
php -i | grep memory_limit

# Zvýš ak potrebné
echo "memory_limit = 256M" >> /etc/php/8.3/apache2/php.ini

# Optimalizuj autoloader
composer dump-autoload --optimize --classmap-authoritative
```

## 🛠️ Debugging Tools

### Error Logging

```bash
# Zapni PHP error logging
echo "log_errors = On" >> /etc/php/8.3/apache2/php.ini
echo "error_log = /var/log/php_errors.log" >> /etc/php/8.3/apache2/php.ini

# Sleduj error log
tail -f /var/log/php_errors.log
```

### Debug Mode

```php
// config/autoload/development.local.php
return [
    'debug' => true,
    'config_cache_enabled' => false,
];
```

### Network Debugging

```bash
# Test connectivity
ping your-domain.com

# Test SSL
openssl s_client -connect your-domain.com:443

# Test headers
curl -I -H "Accept-Encoding: gzip" https://your-domain.com
```

## 📋 Diagnostic Checklist

### Pre každý problém skontroluj:

- [ ] PHP error log (`tail -f /var/log/php_errors.log`)
- [ ] Apache error log (`tail -f /var/log/apache2/error.log`)
- [ ] File permissions (755 pre adresáre, 644 pre súbory)
- [ ] Apache modules (rewrite, headers, deflate)
- [ ] .htaccess súbory existujú a sú čitateľné
- [ ] Database súbory existujú a sú prístupné
- [ ] Built assets existujú v public/themes/
- [ ] Composer dependencies nainštalované

### Emergency Reset

```bash
# Kompletný reset aplikácie
rm -rf vendor/ data/*.db public/themes/*/assets/
composer install
php bin/migrate.php
composer build:themes:prod
```

---

## 📚 Súvisiace Dokumenty

### ⚙️ Konfigurácia a Nastavenia
- **[CONFIGURATION.md](CONFIGURATION.md)** - Konfiguračné možnosti a nastavenia
- **[SECURITY_GUIDE.md](SECURITY_GUIDE.md)** - Bezpečnostné nastavenia a riešenia
- **[APACHE_GUIDE.md](APACHE_GUIDE.md)** - Apache konfigurácia a optimalizácie

### 🚀 Deployment a Production
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Production deployment problémy
- **[MAINTENANCE.md](MAINTENANCE.md)** - Údržba a monitoring
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Architektúra a build systém

## 🎨 Template Problémy

### Template Inkonzistencie (Vyriešené)

**Problém:** Duplicitné template súbory a inkonzistentná štruktúra medzi User a Mark modulmi.

**Identifikované problémy:**
- ❌ Duplicitný `templates/mark-login.phtml` (odstránený)
- ❌ Jazykové inkonzistencie (User: sk, Mark: mix en/sk)
- ❌ Escape function mix (`$this->escapeHtml()` vs `htmlspecialchars()`)
- ❌ Asset loading inkonzistencie (CDN vs local)

**Riešenie:**
```bash
# 1. Odstránenie duplicitných súborov
rm templates/mark-login.phtml

# 2. Zjednotenie jazykov na slovenčinu
# 3. Štandardizácia escape funkcií na $this->escapeHtml()
# 4. Konzistentná štruktúra templates
```

**Template Path Priority:**
```php
// config/autoload/templates.global.php
'paths' => [
    'mark' => ['modules/Mark/templates/mark'],     // Priorita 1
    'user' => ['modules/User/templates/user'],     // Priorita 1
    'app' => ['templates/app'],                    // Priorita 2
    'default' => ['templates'],                    // Priorita 3 (fallback)
],
```

**Aktuálny stav:**
- ✅ Všetky templates používajú `lang="sk"`
- ✅ Konzistentné `$this->escapeHtml()` funkcie
- ✅ Slovenské texty v UI
- ✅ Žiadne duplicitné súbory

### 📖 Základné Návody
- **[QUICK_START.md](QUICK_START.md)** - Rýchly štart a základné problémy
- **[USER_MODULE.md](USER_MODULE.md)** - User modul troubleshooting
- **[API_REFERENCE.md](API_REFERENCE.md)** - API dokumentácia

**Potrebuješ ďalšiu pomoc?** Vytvor issue alebo kontaktuj support.
**Späť na hlavnú:** [README.md](README.md)
