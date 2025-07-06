# ⏰ HDM Boot Protocol - Crontab Configuration

Konfigurácia pravidelných maintenance skriptov pre production prostredie.

**Status:** ✅ Production Ready v2.0.1
**Posledná aktualizácia:** 2025-07-06

## 📋 Pravidelné Maintenance Scripty

### Odporúčané cron jobs:

```bash
# HDM Boot Protocol Maintenance Scripts
# Add to crontab with: crontab -e

# Health check - každých 5 minút
*/5 * * * * /usr/bin/php /path/to/your/app/bin/health-check.php >> /var/log/hdm-health.log 2>&1

# Cache cleanup - denne o 2:00
0 2 * * * /usr/bin/php /path/to/your/app/bin/cleanup-cache.php >> /var/log/hdm-cleanup.log 2>&1

# Database backup - denne o 3:00
0 3 * * * /usr/bin/php /path/to/your/app/bin/backup-databases.php >> /var/log/hdm-backup.log 2>&1

# Database maintenance - každú nedeľu o 4:00
0 4 * * 0 /usr/bin/php /path/to/your/app/bin/maintenance-db.php >> /var/log/hdm-maintenance.log 2>&1

# Log rotation - mesačne
0 5 1 * * find /path/to/your/app/var/logs -name "*.log" -mtime +30 -delete
```

## Nastavenie pre shared hosting:

```bash
# Pre shared hosting (ak máte prístup k cron jobs)
# Upravte cesty podľa vášho hostingu

# Health check - každých 15 minút (menej frequent pre shared hosting)
*/15 * * * * php /home/username/public_html/bin/health-check.php

# Cache cleanup - denne o 3:00
0 3 * * * php /home/username/public_html/bin/cleanup-cache.php

# Database backup - každý druhý deň o 4:00
0 4 */2 * * php /home/username/public_html/bin/backup-databases.php

# Database maintenance - každú nedeľu o 5:00
0 5 * * 0 php /home/username/public_html/bin/maintenance-db.php
```

## Monitoring a alerting:

### 1. Health Check Monitoring
```bash
# Skript na kontrolu health check výsledkov
#!/bin/bash
HEALTH_RESULT=$(php /path/to/app/bin/health-check.php)
if [ $? -ne 0 ]; then
    echo "HDM Boot Protocol Health Check FAILED" | mail -s "Health Check Alert" admin@yourdomain.com
fi
```

### 2. Backup Verification
```bash
# Skript na overenie, že backup sa vytvoril
#!/bin/bash
BACKUP_DIR="/path/to/app/var/storage/backups"
TODAY=$(date +%Y-%m-%d)
if [ ! -f "$BACKUP_DIR/hdm_backup_${TODAY}_*.tar.gz" ]; then
    echo "HDM Boot Protocol Backup MISSING for $TODAY" | mail -s "Backup Alert" admin@yourdomain.com
fi
```

## Logrotate konfigurácia:

```bash
# /etc/logrotate.d/hdm-boot-protocol
/path/to/your/app/var/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        # Restart application if needed
        # systemctl reload your-app
    endscript
}
```

## Disk space monitoring:

```bash
# Skript na monitoring disk space
#!/bin/bash
USAGE=$(df /path/to/app | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $USAGE -gt 85 ]; then
    echo "HDM Boot Protocol: Disk usage is ${USAGE}%" | mail -s "Disk Space Alert" admin@yourdomain.com
fi
```

## Bezpečnostné odporúčania:

1. **Permissions**: Všetky scripty by mali mať správne permissions (755)
2. **Logging**: Všetky cron jobs by mali logovať do separátnych súborov
3. **Error handling**: Monitorujte exit codes scriptov
4. **Notifications**: Nastavte email notifikácie pre kritické chyby
5. **Backup verification**: Pravidelne testujte, že backupy sa dajú obnoviť

## Testovanie cron jobs:

```bash
# Test všetkých maintenance scriptov
php bin/health-check.php
php bin/cleanup-cache.php
php bin/backup-databases.php
php bin/maintenance-db.php
```

## Troubleshooting:

### Ak health check failuje:
1. Skontrolujte database connections
2. Overte file permissions
3. Skontrolujte disk space
4. Pozrite system logs

### Ak backup failuje:
1. Skontrolujte disk space
2. Overte write permissions
3. Skontrolujte, či tar command je dostupný

### Ak cleanup failuje:
1. Overte write permissions na cache/logs directories
2. Skontrolujte, či súbory nie sú locked

## 📋 Production Deployment Checklist

- [ ] Nastavené cron jobs
- [ ] Logrotate konfigurácia
- [ ] Monitoring scripts
- [ ] Email notifications
- [ ] Backup verification
- [ ] Disk space monitoring
- [ ] Error alerting

---

## 📚 Súvisiace Dokumenty

### 🚀 Production a Deployment
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Production deployment guide
- **[MAINTENANCE.md](MAINTENANCE.md)** - Údržba a monitoring scripts
- **[APACHE_GUIDE.md](APACHE_GUIDE.md)** - Apache konfigurácia

### 🔧 Konfigurácia a Support
- **[CONFIGURATION.md](CONFIGURATION.md)** - Konfiguračné možnosti
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Riešenie cron problémov
- **[SECURITY_GUIDE.md](SECURITY_GUIDE.md)** - Security pre cron jobs

### 📖 Základné Návody
- **[APP_PROTOCOL.md](APP_PROTOCOL.md)** - HDM Boot Protocol
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Systémová architektúra
- **[QUICK_START.md](QUICK_START.md)** - Rýchly štart

**Späť na hlavnú:** [README.md](README.md)
