# 🚀 Quick Start Guide

Detailný návod na spustenie Mezzio Minimal aplikácie od inštalácie po production deployment.

## 📋 Požiadavky

### Minimálne požiadavky
- **PHP 8.3+** (testované s PHP 8.4)
- **Composer** (najnovšia verzia)
- **Node.js 18+** a **pnpm** (pre theme development)

### Odporúčané rozšírenia PHP
```bash
# Skontroluj dostupné rozšírenia
php -m | grep -E "(pdo|sqlite|session|openssl|mbstring|json)"
```

Potrebné rozšírenia:
- `pdo_sqlite` (development databázy)
- `session` (user sessions)
- `openssl` (bezpečnosť)
- `mbstring` (string handling)
- `json` (API responses)

## 🛠️ Inštalácia

### 1. Klonuj repository
```bash
git clone <repository-url>
cd mezzio-minimal
```

### 2. Nainštaluj PHP dependencies
```bash
# Production dependencies
composer install --no-dev --optimize-autoloader

# Development dependencies (ak chceš development tools)
composer install
```

### 3. Inicializuj databázy
```bash
# Vytvorí SQLite databázy a default users
php bin/migrate.php
```

**Výstup by mal byť:**
```
✅ User database initialized: data/user.db
✅ Mark database initialized: data/mark.db
✅ Default users created successfully
```

### 4. Spusti development server
```bash
# Pomocou composer scriptu
composer serve

# Alebo priamo
php -S localhost:8080 -t public/
```

Aplikácia bude dostupná na: **http://localhost:8080**

## 🎨 Theme Development (Voliteľné)

Ak chceš upravovať témy, potrebuješ Node.js:

### Bootstrap Theme
```bash
cd themes/bootstrap
pnpm install
pnpm run dev          # Development server s hot reload
pnpm run build        # Build bez hash
pnpm run build:prod   # Production build s hash
```

### TailwindCSS Theme
```bash
cd themes/main
pnpm install
pnpm run dev          # Development server s hot reload
pnpm run build        # Build bez hash
pnpm run build:prod   # Production build s hash
```

## 👤 Prihlásenie

Po spustení aplikácie môžeš použiť tieto default účty:

| Username | Password | Roles | Prístup |
|----------|----------|-------|---------|
| `admin` | `admin123` | admin, user | Plný prístup |
| `user` | `user123` | user | Štandardný user |
| `mark` | `mark123` | mark, user | Mark systém |

**Prihlásenie:** http://localhost:8080/user/login

## 🚀 Production Build

### Vytvorenie production buildu
```bash
# Kompletný production build
composer build:production:package

# Výsledok v build/production/ (5.8MB)
ls -la build/production/
```

### Deployment na server
```bash
# Upload na server
scp -r build/production/* user@server:/var/www/html/

# Nastav permissions
chmod -R 755 /var/www/html
chmod -R 644 /var/www/html/config /var/www/html/src
```

## 🔧 Dostupné Composer Scripty

### Development
```bash
composer serve                    # Spusti development server
composer build:dev                # Development build (no hash)
composer clean:build              # Vyčisti build adresáre
```

### Production
```bash
composer build:production:package # Production build → build/production/
composer build:staging           # Staging build → build/staging/
composer build:release           # Release archive → build/releases/
```

### Theme Management
```bash
composer clean:themes            # Vyčisti theme assets
composer build:themes:prod       # Build všetky témy s hash
```

### Quality Assurance (ak máš dev dependencies)
```bash
composer check                   # Spusti všetky kontroly
composer test                    # PHPUnit testy
composer cs-check                # Coding standards
composer analyze                 # PHPStan analýza
```

## 🌐 Testovanie Routes

Po spustení otestuj tieto stránky:

### Verejné stránky
- **`/`** - Domovská stránka
- **`/bootstrap-demo`** - Bootstrap theme demo
- **`/main-demo`** - TailwindCSS + Alpine.js demo

### User systém
- **`/user/login`** - Prihlásenie
- **`/user/register`** - Registrácia
- **`/user/dashboard`** - Dashboard (po prihlásení)
- **`/user/admin`** - Admin panel (admin role)

## 🐛 Troubleshooting

### Problém: "Permission denied"
```bash
# Nastav správne permissions
chmod -R 755 data/
chmod -R 755 public/themes/
```

### Problém: "Database not found"
```bash
# Znovu spusti migrácie
php bin/migrate.php
```

### Problém: "Assets not loading"
```bash
# Rebuild themes
cd themes/bootstrap && pnpm run build:prod
cd themes/main && pnpm run build:prod
```

### Problém: "500 Internal Server Error"
```bash
# Skontroluj PHP error log
tail -f /var/log/php_errors.log

# Alebo zapni error reporting
echo "display_errors = On" >> php.ini
```

## 📚 Ďalšie kroky

### 1. Prečítaj dokumentáciu:
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Architektúra a theme systém
- **[USER_MODULE.md](USER_MODULE.md)** - User management a autentifikácia
- **[SECURITY_GUIDE.md](SECURITY_GUIDE.md)** - Bezpečnostné best practices

### 2. Production deployment:
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Kompletný deployment guide
- **[APACHE_GUIDE.md](APACHE_GUIDE.md)** - Apache konfigurácia a optimalizácie

### 3. Customizácia a konfigurácia:
- **[CONFIGURATION.md](CONFIGURATION.md)** - Konfiguračné možnosti
- **[API_REFERENCE.md](API_REFERENCE.md)** - API dokumentácia
- **[ACCESSIBILITY.md](ACCESSIBILITY.md)** - SEO a accessibility

### 4. Údržba a monitoring:
- **[MAINTENANCE.md](MAINTENANCE.md)** - Údržba a monitoring
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Riešenie problémov

---

**Potrebuješ pomoc?** Pozri [TROUBLESHOOTING.md](TROUBLESHOOTING.md) alebo vytvor issue.
**Späť na hlavnú:** [README.md](README.md)
