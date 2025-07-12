# 🌐 Apache Configuration Guide

Kompletný návod na konfiguráciu Apache servera pre Mezzio Minimal aplikáciu.

## 📋 Prehľad .htaccess Súborov

Projekt obsahuje kompletnú .htaccess konfiguráciu pre production bezpečnosť a výkon:

### 1. Root .htaccess (`/.htaccess`)
- **Účel**: Deny access to all root files and redirect to public/
- **Funkcie**:
  - Blokuje prístup k config/, src/, vendor/, themes/, data/
  - Presmerováva root requesty na public/ adresár
  - Security headers fallback

### 2. Public .htaccess (`/public/.htaccess`)
- **Účel**: Hlavná aplikačná konfigurácia
- **Funkcie**:
  - **Security Headers**: XSS, CSRF, Clickjacking protection
  - **Caching**: Long-term cache pre versioned assets (1 rok)
  - **Compression**: Gzip compression pre všetky text súbory
  - **URL Rewriting**: Routes všetky requesty na index.php
  - **MIME Types**: Správne content types pre web fonts a assets

## 🔧 Požadované Apache Moduly

Uistite sa, že tieto Apache moduly sú povolené:

```apache
# Požadované moduly
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule headers_module modules/mod_headers.so
LoadModule expires_module modules/mod_expires.so
LoadModule deflate_module modules/mod_deflate.so
LoadModule mime_module modules/mod_mime.so
```

### Povolenie modulov na Ubuntu/Debian:
```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod expires
sudo a2enmod deflate
sudo a2enmod mime
sudo systemctl restart apache2
```

### Povolenie modulov na CentOS/RHEL:
```bash
# Moduly sú zvyčajne už povolené
# Skontroluj v /etc/httpd/conf/httpd.conf
```

## 🏗️ Virtual Host Konfigurácia

### Production Virtual Host

```apache
<VirtualHost *:443>
    ServerName your-domain.com
    DocumentRoot /var/www/mezzio-app/public
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512
    
    # Security Headers (dodatočné k .htaccess)
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Enable .htaccess
    <Directory "/var/www/mezzio-app">
        AllowOverride All
        Require all granted
    </Directory>
    
    # Public directory configuration
    <Directory "/var/www/mezzio-app/public">
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
    </Directory>
    
    # Block access to sensitive directories
    <DirectoryMatch "/(config|src|vendor|themes|data|docs)">
        Require all denied
    </DirectoryMatch>
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/mezzio_error.log
    CustomLog ${APACHE_LOG_DIR}/mezzio_access.log combined
    LogLevel warn
</VirtualHost>

# HTTP to HTTPS redirect
<VirtualHost *:80>
    ServerName your-domain.com
    Redirect permanent / https://your-domain.com/
</VirtualHost>
```

### Development Virtual Host

```apache
<VirtualHost *:80>
    ServerName mezzio-minimal.local
    DocumentRoot /var/www/mezzio-app/public
    
    # Enable .htaccess
    <Directory "/var/www/mezzio-app">
        AllowOverride All
        Require all granted
    </Directory>
    
    # Public directory
    <Directory "/var/www/mezzio-app/public">
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
    </Directory>
    
    # Development - allow access to some directories for debugging
    <Directory "/var/www/mezzio-app/docs">
        AllowOverride None
        Require all granted
    </Directory>
    
    # Still block sensitive directories
    <DirectoryMatch "/(config|src|vendor|data)">
        Require all denied
    </DirectoryMatch>
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/mezzio_dev_error.log
    CustomLog ${APACHE_LOG_DIR}/mezzio_dev_access.log combined
</VirtualHost>
```

## 🔒 Security Features

### Applied Security Headers

```apache
# V public/.htaccess
Header always set X-Content-Type-Options "nosniff"
Header always set X-XSS-Protection "1; mode=block"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob: https://picsum.photos; font-src 'self'"
```

### Directory Protection

```apache
# Root .htaccess
<DirectoryMatch "/(config|src|vendor|themes|data|docs)">
    Require all denied
</DirectoryMatch>

# Blokuje prístup k citlivým súborom
<FilesMatch "\.(env|ini|log|sh)$">
    Require all denied
</FilesMatch>
```

## ⚡ Performance Optimizations

### Caching Strategy

```apache
# V public/.htaccess

# Versioned assets - 1 rok cache
<FilesMatch "\.(css|js)$">
    ExpiresActive On
    ExpiresDefault "access plus 1 year"
    Header append Cache-Control "public, immutable"
</FilesMatch>

# Images - 1 mesiac cache
<FilesMatch "\.(jpg|jpeg|png|gif|webp|svg|ico)$">
    ExpiresActive On
    ExpiresDefault "access plus 1 month"
    Header append Cache-Control "public"
</FilesMatch>

# Fonts - 1 rok cache
<FilesMatch "\.(woff|woff2|ttf|eot)$">
    ExpiresActive On
    ExpiresDefault "access plus 1 year"
    Header append Cache-Control "public"
</FilesMatch>

# Manifest files - 1 deň cache
<FilesMatch "manifest\.json$">
    ExpiresActive On
    ExpiresDefault "access plus 1 day"
    Header append Cache-Control "public"
</FilesMatch>
```

### Gzip Compression

```apache
# V public/.htaccess
<IfModule mod_deflate.c>
    # Compress text files
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE text/javascript
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE application/json
    
    # Exclude already compressed files
    SetEnvIfNoCase Request_URI \
        \.(?:gif|jpe?g|png|rar|zip|exe|flv|mov|wma|mp3|avi|swf|mp?g|mp4|webm|webp)$ no-gzip dont-vary
</IfModule>
```

## 🧪 Testovanie Konfigurácie

### Test Security Headers

```bash
# Test security headers
curl -I https://your-domain.com

# Očakávané headers:
# X-Content-Type-Options: nosniff
# X-XSS-Protection: 1; mode=block
# X-Frame-Options: SAMEORIGIN
# Content-Security-Policy: default-src 'self'...
```

### Test Compression

```bash
# Test gzip compression
curl -H "Accept-Encoding: gzip" -I https://your-domain.com

# Očakávaný header:
# Content-Encoding: gzip
```

### Test Directory Protection

```bash
# Test blokovaných adresárov
curl -I https://your-domain.com/config/
curl -I https://your-domain.com/vendor/
curl -I https://your-domain.com/src/

# Očakávaný výsledok: 403 Forbidden
```

### Test Asset Caching

```bash
# Test cache headers pre assets
curl -I https://your-domain.com/themes/bootstrap/assets/main-D30XL3Ms.css

# Očakávané headers:
# Cache-Control: public, immutable
# Expires: (1 rok do budúcnosti)
```

## 🔧 Troubleshooting

### Problém: "Internal Server Error"

1. **Skontroluj Apache error log:**
```bash
tail -f /var/log/apache2/error.log
```

2. **Skontroluj .htaccess syntax:**
```bash
apache2ctl configtest
```

3. **Skontroluj permissions:**
```bash
ls -la /var/www/mezzio-app/
# Adresáre: 755, súbory: 644
```

### Problém: "Assets sa nenačítavaju"

1. **Skontroluj mod_rewrite:**
```bash
apache2ctl -M | grep rewrite
```

2. **Skontroluj AllowOverride:**
```apache
<Directory "/var/www/mezzio-app/public">
    AllowOverride All  # Nie None!
</Directory>
```

### Problém: "Security headers chýbajú"

1. **Skontroluj mod_headers:**
```bash
apache2ctl -M | grep headers
```

2. **Test headers:**
```bash
curl -I https://your-domain.com
```

## 📋 Production Checklist

- [ ] Apache moduly povolené (rewrite, headers, expires, deflate)
- [ ] Virtual host nakonfigurovaný
- [ ] SSL certifikát nainštalovaný
- [ ] .htaccess súbory na mieste
- [ ] Directory permissions nastavené (755/644)
- [ ] Security headers fungujú
- [ ] Gzip compression zapnuté
- [ ] Cache headers nastavené
- [ ] Directory access blokovaný
- [ ] Error/access logy nakonfigurované

## 🔄 .htaccess vs PHP Built-in Server

### PHP Built-in Server (Development)
- Číta .htaccess súbory a aplikuje obmedzenia
- `Require all denied` blokuje prístup úplne
- Potrebné len root a public .htaccess

### Apache Server (Production)
- .htaccess súbory poskytujú bezpečnostné vrstvy
- Directory protection zabráni priamemu prístupu
- Všetky .htaccess súbory odporúčané pre bezpečnosť

### Obnovenie Directory Protection pre Apache

Ak deploying na Apache server, obnov ochranné .htaccess súbory:

```bash
# Vytvor ochranné .htaccess súbory pre Apache
echo "Require all denied" > config/.htaccess
echo "Require all denied" > src/.htaccess  
echo "Require all denied" > vendor/.htaccess
echo "Require all denied" > themes/.htaccess
echo "Require all denied" > data/.htaccess
```

---

**Ďalšie informácie:**
- [SECURITY_GUIDE.md](SECURITY_GUIDE.md) - Bezpečnostné best practices
- [DEPLOYMENT.md](DEPLOYMENT.md) - Production deployment
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Riešenie problémov
