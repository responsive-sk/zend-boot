# Rýchly štart

Návod na spustenie Mezzio Minimal aplikácie od inštalácie po production.

## Požiadavky

- **PHP 8.3+** (testované s PHP 8.4)
- **Composer** (najnovšia verzia)
- **Node.js 18+** a **pnpm** (pre theme development)

### PHP rozšírenia
```bash
# Skontroluj rozšírenia
php -m | grep -E "(pdo|sqlite|session|openssl|mbstring|json)"
```

Potrebné: `pdo_sqlite`, `session`, `openssl`, `mbstring`, `json`

## Inštalácia

### 1. Klonuj repository
```bash
git clone <repository-url>
cd mezzio-minimal
```

### 2. Nainštaluj dependencies
```bash
# Production
composer install --no-dev --optimize-autoloader

# Development (s dev tools)
composer install
```

### 3. Inicializuj databázy
```bash
php bin/migrate.php
```

**Očakávaný výstup:**
```
User database initialized: data/user.db
Mark database initialized: data/mark.db
Default users created successfully
```

### 4. Spusti server
```bash
composer serve
# Alebo: php -S localhost:8080 -t public/
```

Aplikácia: **http://localhost:8080**

## Prihlásenie

| Username | Password | Roles | Prístup |
|----------|----------|-------|---------|
| `admin` | `admin123` | admin, user | Plný prístup |
| `user` | `user123` | user | Štandardný |
| `mark` | `mark123` | mark, user | Mark systém |

**Prihlásenie:** http://localhost:8080/user/login

## Theme Development (Voliteľné)

### Bootstrap Theme
```bash
cd themes/bootstrap
pnpm install
pnpm run dev          # Development s hot reload
pnpm run build:prod   # Production build s hash
```

### TailwindCSS Theme
```bash
cd themes/main
pnpm install
pnpm run dev          # Development s hot reload
pnpm run build:prod   # Production build s hash
```

## Production Build

```bash
# Kompletný production build
composer build:production:package

# Výsledok v build/production/ (5.8MB)
ls -la build/production/
```

### Deployment
```bash
# Upload na server
scp -r build/production/* user@server:/var/www/html/

# Nastav permissions
chmod -R 755 /var/www/html
chmod -R 644 /var/www/html/config /var/www/html/src
```

## Composer Scripty

### Development
```bash
composer serve                    # Development server
composer build:dev                # Development build
composer clean:build              # Vyčisti build
```

### Production
```bash
composer build:production:package # Production → build/production/
composer build:staging           # Staging → build/staging/
composer build:release           # Release archive
```

### Quality Assurance
```bash
composer check                   # Všetky kontroly
composer test                    # PHPUnit testy
composer cs-check                # Coding standards
composer analyze                 # PHPStan analýza
```

## Testovanie

### Verejné stránky
- **`/`** - Domovská stránka
- **`/bootstrap-demo`** - Bootstrap theme
- **`/main-demo`** - TailwindCSS + Alpine.js

### User systém
- **`/user/login`** - Prihlásenie
- **`/user/register`** - Registrácia
- **`/user/dashboard`** - Dashboard
- **`/user/admin`** - Admin panel

## Časté problémy

### Permission denied
```bash
chmod -R 755 data/ public/themes/
```

### Database not found
```bash
php bin/migrate.php
```

### Assets not loading
```bash
cd themes/bootstrap && pnpm run build:prod
cd themes/main && pnpm run build:prod
```

### 500 Internal Server Error
```bash
# Skontroluj PHP error log
tail -f /var/log/php_errors.log
```

## Ďalšie kroky

1. **Prečítaj dokumentáciu:**
   - [ARCHITEKTURA.md](ARCHITEKTURA.md) - Architektúra systému
   - [USER_MODUL.md](USER_MODUL.md) - User management
   - [BEZPECNOST.md](BEZPECNOST.md) - Bezpečnosť

2. **Production deployment:**
   - [DEPLOYMENT.md](DEPLOYMENT.md) - Deployment guide
   - [../APACHE_GUIDE.md](../APACHE_GUIDE.md) - Apache konfigurácia

3. **Konfigurácia:**
   - [KONFIGURACIA.md](KONFIGURACIA.md) - Nastavenia
   - [../API_REFERENCE.md](../API_REFERENCE.md) - API docs

---

**Potrebuješ pomoc?** Pozri [RIESENIE_PROBLEMOV.md](RIESENIE_PROBLEMOV.md)
**Späť:** [README.md](README.md)
