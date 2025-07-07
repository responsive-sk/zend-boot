# 🔧 Maintenance & Monitoring Guide

Kompletný návod na údržbu a monitoring Mezzio Minimal aplikácie.

## 📋 Prehľad Údržby

Pravidelná údržba zabezpečuje optimálny výkon, bezpečnosť a stabilitu aplikácie.

### Údržbové Úlohy

| Frekvencia | Úloha | Popis |
|------------|-------|-------|
| **Denne** | Log monitoring | Kontrola error logov |
| **Týždenne** | Database cleanup | Vyčistenie starých sessions |
| **Mesačne** | Security updates | Aktualizácia dependencies |
| **Štvrťročne** | Performance audit | Lighthouse a Core Web Vitals |
| **Ročne** | SSL renewal | Obnovenie SSL certifikátov |

## 📊 Monitoring Setup

### 1. Log Monitoring

#### Apache Access Log
```bash
# Sledovanie real-time prístupu
tail -f /var/log/apache2/access.log

# Analýza najčastejších IP adries
awk '{print $1}' /var/log/apache2/access.log | sort | uniq -c | sort -nr | head -10

# Analýza response codes
awk '{print $9}' /var/log/apache2/access.log | sort | uniq -c | sort -nr
```

#### Apache Error Log
```bash
# Sledovanie chýb
tail -f /var/log/apache2/error.log

# Počet chýb za posledných 24 hodín
grep "$(date '+%Y-%m-%d')" /var/log/apache2/error.log | wc -l
```

#### PHP Error Log
```bash
# Sledovanie PHP chýb
tail -f /var/log/php_errors.log

# Najčastejšie PHP chyby
grep "PHP Fatal error" /var/log/php_errors.log | sort | uniq -c | sort -nr
```

### 2. Performance Monitoring

#### Server Resources
```bash
# CPU a Memory usage
htop

# Disk usage
df -h
du -sh /var/www/mezzio-app/

# Apache processes
ps aux | grep apache2
```

#### Database Monitoring
```bash
# SQLite database size
ls -lh data/*.db

# Database integrity check
sqlite3 data/user.db "PRAGMA integrity_check;"
sqlite3 data/mark.db "PRAGMA integrity_check;"
```

### 3. Security Monitoring

#### Failed Login Attempts
```bash
# Monitoring failed logins (ak máš logging)
grep "login failed" /var/log/apache2/access.log | tail -20

# Suspicious IP addresses
awk '{print $1}' /var/log/apache2/access.log | sort | uniq -c | sort -nr | head -20
```

#### Security Headers Check
```bash
# Automated security headers test
curl -I https://your-domain.com | grep -E "(X-|Content-Security|Strict-Transport)"
```

## 🧹 Cleanup Scripts

### 1. Session Cleanup

```bash
#!/bin/bash
# cleanup-sessions.sh

echo "🧹 Cleaning up old sessions..."

# Remove sessions older than 24 hours
find data/sessions/ -name "sess_*" -mtime +1 -delete 2>/dev/null || true

# SQLite vacuum (optimize database)
sqlite3 data/user.db "VACUUM;"
sqlite3 data/mark.db "VACUUM;"

echo "✅ Session cleanup completed"
```

### 2. Log Rotation

```bash
#!/bin/bash
# rotate-logs.sh

echo "🔄 Rotating application logs..."

# Rotate PHP error log
if [ -f /var/log/php_errors.log ]; then
    mv /var/log/php_errors.log /var/log/php_errors.log.$(date +%Y%m%d)
    touch /var/log/php_errors.log
    chmod 644 /var/log/php_errors.log
fi

# Keep only last 30 days of logs
find /var/log/ -name "php_errors.log.*" -mtime +30 -delete

echo "✅ Log rotation completed"
```

### 3. Cache Cleanup

```bash
#!/bin/bash
# cleanup-cache.sh

echo "🗑️ Cleaning up cache files..."

# Clear OPcache (if using)
php -r "if (function_exists('opcache_reset')) opcache_reset();"

# Clear any temporary files
find /tmp -name "php*" -mtime +1 -delete 2>/dev/null || true

# Clear old build files
find build/ -name "*.tar.gz" -mtime +7 -delete 2>/dev/null || true

echo "✅ Cache cleanup completed"
```

## ⏰ Cron Jobs Setup

### Crontab Configuration

```bash
# Edituj crontab
crontab -e

# Pridaj tieto úlohy:

# Daily session cleanup at 2 AM
0 2 * * * /var/www/mezzio-app/scripts/cleanup-sessions.sh >> /var/log/maintenance.log 2>&1

# Weekly log rotation on Sunday at 3 AM  
0 3 * * 0 /var/www/mezzio-app/scripts/rotate-logs.sh >> /var/log/maintenance.log 2>&1

# Daily cache cleanup at 4 AM
0 4 * * * /var/www/mezzio-app/scripts/cleanup-cache.sh >> /var/log/maintenance.log 2>&1

# Weekly database optimization on Sunday at 5 AM
0 5 * * 0 /var/www/mezzio-app/scripts/optimize-database.sh >> /var/log/maintenance.log 2>&1
```

### Database Optimization Script

```bash
#!/bin/bash
# optimize-database.sh

echo "🔧 Optimizing databases..."

# SQLite optimization
sqlite3 data/user.db "PRAGMA optimize;"
sqlite3 data/user.db "PRAGMA wal_checkpoint(TRUNCATE);"

sqlite3 data/mark.db "PRAGMA optimize;"  
sqlite3 data/mark.db "PRAGMA wal_checkpoint(TRUNCATE);"

# Analyze database statistics
sqlite3 data/user.db "ANALYZE;"
sqlite3 data/mark.db "ANALYZE;"

echo "✅ Database optimization completed"
```

## 📈 Performance Optimization

### 1. PHP Optimization

```ini
# /etc/php/8.3/apache2/php.ini

# OPcache settings
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1

# Memory settings
memory_limit=256M
max_execution_time=30
max_input_time=60

# Session settings
session.gc_maxlifetime=3600
session.gc_probability=1
session.gc_divisor=100
```

### 2. Apache Optimization

```apache
# /etc/apache2/conf-available/performance.conf

# Enable compression
LoadModule deflate_module modules/mod_deflate.so
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>

# Enable caching
LoadModule expires_module modules/mod_expires.so
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
</IfModule>

# Connection settings
KeepAlive On
MaxKeepAliveRequests 100
KeepAliveTimeout 5
```

### 3. Database Optimization

```sql
-- SQLite optimization commands

-- Enable WAL mode for better concurrency
PRAGMA journal_mode=WAL;

-- Optimize page size
PRAGMA page_size=4096;

-- Enable foreign keys
PRAGMA foreign_keys=ON;

-- Set cache size (in pages)
PRAGMA cache_size=10000;

-- Optimize for speed
PRAGMA synchronous=NORMAL;
PRAGMA temp_store=MEMORY;
```

## 🔍 Health Checks

### 1. Application Health Check Script

```bash
#!/bin/bash
# health-check.sh

echo "🏥 Running application health check..."

# Check web server response
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/)
if [ "$HTTP_CODE" != "200" ]; then
    echo "❌ Web server not responding (HTTP $HTTP_CODE)"
    exit 1
fi

# Check database connectivity
if ! sqlite3 data/user.db "SELECT 1;" > /dev/null 2>&1; then
    echo "❌ User database not accessible"
    exit 1
fi

if ! sqlite3 data/mark.db "SELECT 1;" > /dev/null 2>&1; then
    echo "❌ Mark database not accessible"  
    exit 1
fi

# Check disk space
DISK_USAGE=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$DISK_USAGE" -gt 90 ]; then
    echo "⚠️ Disk usage high: ${DISK_USAGE}%"
fi

# Check memory usage
MEM_USAGE=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')
if [ "$MEM_USAGE" -gt 90 ]; then
    echo "⚠️ Memory usage high: ${MEM_USAGE}%"
fi

echo "✅ Health check completed successfully"
```

### 2. Security Check Script

```bash
#!/bin/bash
# security-check.sh

echo "🔒 Running security check..."

# Check file permissions
find /var/www/mezzio-app -type f -perm /o+w -exec echo "⚠️ World-writable file: {}" \;

# Check for sensitive files in web root
if find public/ -name "*.log" -o -name "*.sql" -o -name "*.local.php" | grep -q .; then
    echo "❌ Sensitive files found in public directory"
fi

# Check SSL certificate expiry (if HTTPS)
if command -v openssl >/dev/null; then
    CERT_DAYS=$(echo | openssl s_client -connect your-domain.com:443 2>/dev/null | openssl x509 -noout -dates | grep notAfter | cut -d= -f2 | xargs -I {} date -d {} +%s)
    CURRENT_DAYS=$(date +%s)
    DAYS_LEFT=$(( (CERT_DAYS - CURRENT_DAYS) / 86400 ))
    
    if [ "$DAYS_LEFT" -lt 30 ]; then
        echo "⚠️ SSL certificate expires in $DAYS_LEFT days"
    fi
fi

echo "✅ Security check completed"
```

## 📊 Monitoring Dashboard

### Simple Status Page

```php
<?php
// public/status.php - Simple status endpoint

header('Content-Type: application/json');

$status = [
    'timestamp' => date('c'),
    'status' => 'ok',
    'checks' => []
];

// Database check
try {
    $pdo = new PDO('sqlite:../data/user.db');
    $pdo->query('SELECT 1');
    $status['checks']['database'] = 'ok';
} catch (Exception $e) {
    $status['checks']['database'] = 'error';
    $status['status'] = 'error';
}

// Disk space check
$diskFree = disk_free_space('/');
$diskTotal = disk_total_space('/');
$diskUsage = round((1 - $diskFree / $diskTotal) * 100, 2);
$status['checks']['disk_usage'] = $diskUsage . '%';

if ($diskUsage > 90) {
    $status['status'] = 'warning';
}

echo json_encode($status, JSON_PRETTY_PRINT);
```

## 📋 Maintenance Checklist

### Denná Údržba
- [ ] Skontrolovať error logy
- [ ] Overiť dostupnosť aplikácie
- [ ] Skontrolovať disk space
- [ ] Monitoring failed requests

### Týždenná Údržba  
- [ ] Vyčistiť staré session súbory
- [ ] Rotovať logy
- [ ] Optimalizovať databázy
- [ ] Skontrolovať security headers

### Mesačná Údržba
- [ ] Aktualizovať PHP dependencies (`composer update`)
- [ ] Aktualizovať Node.js dependencies (`pnpm update`)
- [ ] Skontrolovať SSL certifikát
- [ ] Performance audit (Lighthouse)
- [ ] Security scan

### Štvrťročná Údržba
- [ ] Kompletný backup databáz
- [ ] Aktualizovať PHP verziu
- [ ] Aktualizovať Apache/server
- [ ] Penetration testing
- [ ] Disaster recovery test

---

**Ďalšie informácie:**
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Riešenie problémov
- [SECURITY_GUIDE.md](SECURITY_GUIDE.md) - Bezpečnostné monitoring
- [DEPLOYMENT.md](DEPLOYMENT.md) - Production deployment
