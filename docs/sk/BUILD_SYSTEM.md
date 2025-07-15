# Build System - DotKernel Light

Nový build systém kompatibilný so slim4-paths v6.0 a var/ directory štruktúrou.

## Prehľad

Build systém bol kompletne prepísaný pre kompatibilitu s:
- **slim4-paths v6.0** - nové API a lazy loading
- **var/ directory štruktúra** - moderný štandard pre runtime súbory
- **Memory efficiency** - 98% úspora pamäte oproti v5.0
- **Built-in security** - path traversal protection

## Použitie

### Základné použitie

```bash
# Production build (default)
./bin/build.sh

# Alebo explicitne
./bin/build.sh production
```

### Shared hosting builds

```bash
# Shared hosting build
./bin/build.sh shared-hosting

# Minimálny shared hosting build
./bin/build.sh shared-hosting-minimal
```

### Priame PHP spustenie

```bash
# Ak bash nie je dostupný
php bin/build-production.php production
php bin/build-production.php shared-hosting
php bin/build-production.php shared-hosting-minimal
```

## Build targety

### 1. Production
- **Účel**: Plný production build pre VPS/dedicated servery
- **Obsahuje**: Všetky súbory, dokumentáciu, všetky bin scripty
- **Optimalizácie**: Autoloader optimization, production dependencies
- **Veľkosť**: Najväčší package

### 2. Shared Hosting
- **Účel**: Build pre shared hosting s obmedzenými funkciami
- **Obsahuje**: Aplikáciu bez dev súborov, základnú dokumentáciu
- **Optimalizácie**: Vendor cleanup, permission handling
- **Veľkosť**: Stredný package

### 3. Shared Hosting Minimal
- **Účel**: Minimálny build pre najslabšie shared hosting
- **Obsahuje**: Len nevyhnutné súbory pre chod aplikácie
- **Optimalizácie**: Maximálny vendor cleanup, minimálne bin scripty
- **Veľkosť**: Najmenší package

## Zmeny oproti starému systému

### Kompatibilita so slim4-paths v6.0

#### Staré API (v5.0)
```php
$this->paths->logs('app.log')
$this->paths->cache('config')
$this->paths->data('uploads')
```

#### Nové API (v6.0)
```php
$this->paths->buildPath('var/logs/app.log')
$this->paths->getPath('cache') . '/config'
$this->paths->getPath('data') . '/uploads'
```

### Directory štruktúra

#### Pred (data/ a log/)
```
project/
├── data/
│   ├── cache/
│   └── uploads/
├── log/
│   └── error-log-*.log
```

#### Po (var/ štruktúra)
```
project/
├── var/
│   ├── data/           # Aplikačné dáta
│   ├── logs/           # Log súbory
│   ├── cache/          # Cache súbory
│   │   ├── config/     # Config cache
│   │   ├── twig/       # Twig cache
│   │   └── routes/     # Route cache
│   ├── tmp/            # Dočasné súbory
│   └── sessions/       # Session súbory
```

## Funkcie build systému

### 1. Automatická detekcia paths
```php
// Build script automaticky načíta konfiguráciu
$config = require 'config/autoload/paths.global.php';
$this->paths = new Paths($basePath);

// Aplikuje custom paths z konfigurácie
foreach ($config['paths']['custom_paths'] as $name => $path) {
    $this->paths->set($name, $path);
}
```

### 2. Var/ directory creation
```php
// Automaticky vytvorí var/ štruktúru
$varDirs = [
    'var/data',
    'var/logs', 
    'var/cache/config',
    'var/cache/twig',
    'var/cache/routes',
    'var/tmp',
    'var/sessions',
    'var/uploads',
];
```

### 3. Frontend assets build
```bash
# Automaticky detekuje package manager
pnpm install && pnpm run build  # Ak je pnpm dostupný
npm install && npm run build    # Fallback na npm
```

### 4. Vendor optimization
```bash
# Pre minimal builds
find vendor/ -name "test*" -type d -exec rm -rf {} +
find vendor/ -name "*.md" -delete
find vendor/ -name "docs" -type d -exec rm -rf {} +
```

## Environment variables

```bash
# Build directory
export BUILD_DIR="./custom-build"

# Package name
export PACKAGE_NAME="my-app"

# Version
export VERSION="1.0.0"

# Spustenie s custom nastaveniami
./bin/build.sh production
```

## Výstup build procesu

### Package súbory
```
build/
├── production/
│   └── dotkernel-light-production_20250715_143022.tar.gz
├── shared-hosting/
│   └── dotkernel-light-shared-hosting_20250715_143022.tar.gz
└── shared-hosting-minimal/
    └── dotkernel-light-shared-hosting-minimal_20250715_143022.tar.gz
```

### Checksum súbory
```
dotkernel-light-production_20250715_143022.tar.gz.sha256
```

### Deployment instructions
```
DEPLOYMENT_INSTRUCTIONS.txt  # V každom package
```

## Validácia build

Build systém automaticky validuje:

### Required files
- `public/index.php`
- `config/config.php`
- `vendor/autoload.php`
- `config/autoload/paths.global.php`

### Required directories (var/ štruktúra)
- `config/autoload`
- `src`
- `vendor`
- `var/data`
- `var/logs`
- `var/cache`
- `var/tmp`

## Troubleshooting

### PHP version error
```bash
Error: PHP 8.2+ required, found: 8.1.0
```
**Riešenie**: Aktualizujte PHP na verziu 8.2+

### Composer not found
```bash
Error: Composer is not installed or not in PATH
```
**Riešenie**: Nainštalujte Composer alebo pridajte do PATH

### Permission errors
```bash
Failed to create directory: /path/to/build
```
**Riešenie**: Skontrolujte permissions na build directory

### Vendor cleanup fails
```bash
Command failed: find vendor/ -name "test*" -type d -exec rm -rf {} +
```
**Riešenie**: Spustite build s sudo alebo skontrolujte permissions

## Performance

### Memory usage
- **v5.0 paths**: ~220KB memory footprint
- **v6.0 paths**: ~4KB memory footprint
- **Úspora**: 98.6%

### Build times
- **Production**: ~2-5 minút
- **Shared hosting**: ~1-3 minúty  
- **Minimal**: ~30-60 sekúnd

### Package sizes
- **Production**: ~50-100MB
- **Shared hosting**: ~30-60MB
- **Minimal**: ~15-30MB

## Migrácia zo starého build systému

### 1. Backup starého scriptu
```bash
mv bin/build-production.sh bin/build-production.sh.backup
```

### 2. Test nového systému
```bash
./bin/build.sh shared-hosting-minimal
```

### 3. Porovnanie výstupov
```bash
# Porovnajte veľkosti a obsah packages
ls -la build/*/
```

### 4. Deployment test
```bash
# Test na staging prostredí
```

Nový build systém je plne kompatibilný so slim4-paths v6.0 a pripravený na produkčné použitie!
