# 🧹 Bin Scripts Cleanup Plan

**Aktuálny stav:** 22 skriptov  
**Cieľ:** ~11 skriptov (50% redukcia)  
**Dátum:** 2025-07-06

## 📊 Analýza Skriptov

### 🔴 Na Odstránenie (11 skriptov)

| Skript | Dôvod Odstránenia | Náhrada/Konzolidácia |
|--------|-------------------|----------------------|
| `build-prod.php` | Duplicitná funkcionalita | `build-production.sh` |
| `build-shared-hosting-fixed.sh` | Špecifická varianta | Konzolidovať do `build-production.sh` |
| `build-shared-hosting-minimal.sh` | Špecifická varianta | Konzolidovať do `build-production.sh` |
| `build-shared-hosting.sh` | Špecifická varianta | Konzolidovať do `build-production.sh` |
| `cleanup-user-db.php` | Špecifická funkcionalita | Konzolidovať do `maintenance-db.php` |
| `init-mark-db.php` | Čiastočná funkcionalita | `init-all-db.php` pokrýva |
| `init-system-db.php` | Čiastočná funkcionalita | `init-all-db.php` pokrýva |
| `init-user-db.php` | Čiastočná funkcionalita | `init-all-db.php` pokrýva |
| `migrate.php` | Minimálna funkcionalita | Konzolidovať do `maintenance-db.php` |
| `test-build.php` | Testing funkcionalita | Použiť composer test |
| `test-hdm-paths.php` | Testing funkcionalita | Konzolidovať do `health-check.php` |

### 🟢 Ponechať (11 skriptov)

| Skript | Dôvod Ponechania | Kategória |
|--------|------------------|-----------|
| `build-dev.sh` | **Hlavný development build** | Build |
| `build-production.sh` | **Hlavný production build** | Build |
| `build-to-directory.sh` | **Flexibilný build systém** | Build |
| `deploy.sh` | **Production deployment** | Deployment |
| `backup-databases.php` | **Kritická funkcionalita** | Maintenance |
| `cleanup-cache.php` | **Pravidelná údržba** | Maintenance |
| `health-check.php` | **System monitoring** | Monitoring |
| `init-all-db.php` | **Master DB initialization** | Database |
| `maintenance-db.php` | **Database maintenance** | Database |
| `migrate-to-hdm-paths.php` | **HDM migration** | Migration |
| `monitor.sh` | **System monitoring** | Monitoring |

## 🔧 Konzolidačný Plán

### 1. Build Scripts Konzolidácia
```bash
# Odstrániť:
rm bin/build-prod.php
rm bin/build-shared-hosting*.sh

# Rozšíriť build-production.sh o shared hosting options:
# --target=shared-hosting
# --target=shared-hosting-minimal
# --target=shared-hosting-fixed
```

### 2. Database Scripts Konzolidácia
```bash
# Odstrániť:
rm bin/init-mark-db.php
rm bin/init-system-db.php  
rm bin/init-user-db.php
rm bin/cleanup-user-db.php
rm bin/migrate.php

# Rozšíriť existujúce:
# init-all-db.php - už pokrýva všetky DB
# maintenance-db.php - rozšíriť o cleanup a migrate funkcie
```

### 3. Testing Scripts Konzolidácia
```bash
# Odstrániť:
rm bin/test-build.php
rm bin/test-hdm-paths.php

# Nahradiť:
# composer test - pre testing
# health-check.php - rozšíriť o HDM paths test
```

## 📋 Implementačné Kroky

### Krok 1: Backup Existujúcich Skriptov
```bash
mkdir -p backup/bin-scripts-$(date +%Y%m%d)
cp bin/* backup/bin-scripts-$(date +%Y%m%d)/
```

### Krok 2: Rozšírenie Ponechaných Skriptov
1. **build-production.sh** - pridať shared hosting options
2. **maintenance-db.php** - pridať cleanup a migrate funkcie  
3. **health-check.php** - pridať HDM paths testing

### Krok 3: Odstránenie Redundantných Skriptov
```bash
# Build scripts
rm bin/build-prod.php
rm bin/build-shared-hosting-fixed.sh
rm bin/build-shared-hosting-minimal.sh
rm bin/build-shared-hosting.sh

# Database scripts  
rm bin/init-mark-db.php
rm bin/init-system-db.php
rm bin/init-user-db.php
rm bin/cleanup-user-db.php
rm bin/migrate.php

# Testing scripts
rm bin/test-build.php
rm bin/test-hdm-paths.php
```

### Krok 4: Aktualizácia Composer.json
```json
{
    "scripts": {
        "build:dev": "./bin/build-dev.sh",
        "build:production": "./bin/build-production.sh",
        "build:production:shared": "./bin/build-production.sh --target=shared-hosting",
        "build:staging": "./bin/build-to-directory.sh staging",
        "deploy": "./bin/deploy.sh",
        "db:init": "./bin/init-all-db.php",
        "db:backup": "./bin/backup-databases.php", 
        "db:maintenance": "./bin/maintenance-db.php",
        "system:health": "./bin/health-check.php",
        "system:monitor": "./bin/monitor.sh",
        "cache:clear": "./bin/cleanup-cache.php"
    }
}
```

## 🎯 Výsledná Štruktúra (11 skriptov)

```
bin/
├── 🏗️  Build & Deployment (4)
│   ├── build-dev.sh              # Development build
│   ├── build-production.sh       # Production build (+ shared hosting)
│   ├── build-to-directory.sh     # Flexible builds
│   └── deploy.sh                 # Production deployment
├── 💾 Database Management (3)
│   ├── init-all-db.php          # Master DB initialization
│   ├── backup-databases.php     # Database backups
│   └── maintenance-db.php       # DB maintenance (+ cleanup, migrate)
├── 🔧 System Maintenance (2)
│   ├── cleanup-cache.php        # Cache cleanup
│   └── migrate-to-hdm-paths.php # HDM migration
└── 📊 Monitoring (2)
    ├── health-check.php         # Health check (+ HDM paths test)
    └── monitor.sh               # System monitoring
```

## 📈 Benefity Cleanup

### Pred Cleanup
- ❌ 22 skriptov (príliš veľa)
- ❌ Duplicitné funkcionality
- ❌ Fragmentovaná štruktúra
- ❌ Ťažká údržba

### Po Cleanup  
- ✅ 11 skriptov (optimálne)
- ✅ Žiadne duplicity
- ✅ Logická štruktúra
- ✅ Jednoduchá údržba
- ✅ 50% redukcia komplexity

## ⚠️ Riziká a Mitigácia

### Riziká
1. **Strata funkcionality** - niektoré špecifické features
2. **Breaking changes** - existujúce workflows
3. **Komplexnejšie skripty** - viac parametrov

### Mitigácia
1. **Backup všetkých skriptov** pred odstránením
2. **Postupná migrácia** funkcionalít
3. **Testovanie** každého kroku
4. **Dokumentácia** nových parametrov

## 🚀 Odporúčanie

**Pokračovať s cleanup plánom?**

Cleanup zníži komplexitu o 50% a zlepší udržiavateľnosť systému pri zachovaní všetkých kľúčových funkcionalít.

**Ďalší krok:** Implementácia cleanup plánu po schválení.
