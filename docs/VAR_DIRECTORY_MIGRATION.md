# Migration to var/ Directory Structure

Tento dokument popisuje migráciu z `data/` a `log/` adresárov na novú `var/` directory štruktúru v súlade s slim4-paths v6.0.

## Dôvody migrácie

### 1. Best Practices
- `var/` directory je štandardom pre runtime súbory v moderných PHP aplikáciách
- Lepšie oddelenie runtime súborov od aplikačného kódu
- Konzistentnosť s frameworkmi ako Symfony, Laravel

### 2. Security
- Runtime súbory sú jasne oddelené od zdrojového kódu
- Ľahšie nastavenie permissions pre web server
- Lepšia organizácia pre deployment

### 3. slim4-paths v6.0
- Nová verzia používa `var/` ako default
- Memory efficiency vyžaduje konzistentnú štruktúru
- Lazy loading presets očakávajú štandardnú štruktúru

## Stará vs Nová štruktúra

### Pred migráciou
```
project/
├── data/
│   ├── cache/
│   │   ├── config-cache.php
│   │   └── twig/
│   └── .gitignore
├── log/
│   ├── error-log-2025-07-14.log
│   └── .gitignore
└── ...
```

### Po migrácii
```
project/
├── var/
│   ├── data/           # Aplikačné dáta
│   ├── cache/          # Cache súbory
│   │   ├── config/     # Config cache
│   │   ├── twig/       # Twig cache
│   │   └── routes/     # Route cache
│   ├── logs/           # Log súbory
│   ├── tmp/            # Dočasné súbory
│   └── sessions/       # Session súbory
└── ...
```

## Kroky migrácie

### 1. Vytvorenie var/ štruktúry

```bash
mkdir -p var/data
mkdir -p var/cache/config
mkdir -p var/cache/twig
mkdir -p var/cache/routes
mkdir -p var/logs
mkdir -p var/tmp
mkdir -p var/sessions
```

### 2. Aktualizácia konfigurácií

#### Error Handling
**Súbor**: `config/autoload/error-handling.global.php`

```php
// Pred
'stream' => __DIR__ . '/../../log/error-log-{Y}-{m}-{d}.log',

// Po
'stream' => __DIR__ . '/../../var/logs/error-log-{Y}-{m}-{d}.log',
```

#### Twig Cache
**Súbor**: `config/autoload/templates.global.php`

```php
// Pred
'cache_dir' => 'data/cache/twig',

// Po
'cache_dir' => 'var/cache/twig',
```

#### Config Cache
**Súbor**: `config/config.php`

```php
// Pred
'config_cache_path' => 'data/cache/config-cache.php',

// Po
'config_cache_path' => 'var/cache/config-cache.php',
```

### 3. Migrácia existujúcich súborov

```bash
# Migrácia cache súborov
mv data/cache/* var/cache/ 2>/dev/null || true

# Migrácia log súborov  
mv log/* var/logs/ 2>/dev/null || true

# Migrácia dát
mv data/* var/data/ 2>/dev/null || true
```

### 4. Aktualizácia .gitignore

```gitignore
# Staré
/data/cache/*
!/data/cache/.gitignore
/log/*
!/log/.gitignore

# Nové
/var/cache/*
!/var/cache/.gitignore
/var/logs/*
!/var/logs/.gitignore
/var/tmp/*
!/var/tmp/.gitignore
```

### 5. Permissions

```bash
# Nastavenie správnych permissions
chmod 755 var/
chmod 755 var/data/
chmod 755 var/cache/
chmod 755 var/logs/
chmod 755 var/tmp/
chmod 755 var/sessions/

# Pre web server
chown -R www-data:www-data var/ # Ubuntu/Debian
# alebo
chown -R apache:apache var/     # CentOS/RHEL
```

## Overenie migrácie

### 1. Paths service test

```php
$paths = $container->get('ResponsiveSk\Slim4Paths\Paths');

echo 'Data: ' . $paths->getPath('data') . PHP_EOL;     // var/data
echo 'Logs: ' . $paths->getPath('logs') . PHP_EOL;     // var/logs  
echo 'Cache: ' . $paths->getPath('cache') . PHP_EOL;   // var/cache
```

### 2. Logging test

```bash
# Spusti aplikáciu a skontroluj log
tail -f var/logs/error-log-$(date +%Y-%m-%d).log
```

### 3. Cache test

```bash
# Vymaž cache a over regeneráciu
rm -rf var/cache/*
# Spusti aplikáciu
ls -la var/cache/
```

## Deployment considerations

### 1. Production deployment

```bash
# Vytvor var/ štruktúru na serveri
mkdir -p var/{data,cache,logs,tmp,sessions}

# Nastav permissions
chmod -R 755 var/
chown -R www-data:www-data var/

# Sync len potrebné súbory
rsync -av --exclude='var/cache/*' --exclude='var/logs/*' ./ server:/path/
```

### 2. Docker

```dockerfile
# Dockerfile
RUN mkdir -p var/{data,cache,logs,tmp,sessions} && \
    chown -R www-data:www-data var/

VOLUME ["/app/var"]
```

### 3. Backup strategy

```bash
# Backup len dôležitých súborov
tar -czf backup.tar.gz var/data/ var/logs/
# Vynechaj cache a tmp
```

## Troubleshooting

### Permission errors
```bash
# Skontroluj ownership
ls -la var/

# Oprav permissions
sudo chown -R www-data:www-data var/
sudo chmod -R 755 var/
```

### Cache issues
```bash
# Vymaž všetky cache súbory
rm -rf var/cache/*

# Reštartuj web server
sudo systemctl restart apache2  # alebo nginx
```

### Log rotation
```bash
# Nastav logrotate pre var/logs/
sudo nano /etc/logrotate.d/myapp
```

## Záver

Migrácia na `var/` directory štruktúru poskytuje:

- ✅ Lepšiu organizáciu súborov
- ✅ Konzistentnosť s modernými štandardmi  
- ✅ Kompatibilitu s slim4-paths v6.0
- ✅ Lepšiu bezpečnosť a permissions management
- ✅ Ľahší deployment a backup

Migrácia je dokončená a aplikácia používa novú štruktúru.
