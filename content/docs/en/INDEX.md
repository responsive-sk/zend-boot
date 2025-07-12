# 🚀 Mezzio Minimal - Production Ready Application

Moderná Mezzio aplikácia s pokročilým theme systémom, user managementom a production optimalizáciami.

## ✨ Kľúčové funkcie

- 🎨 **Dual Theme System** - Bootstrap 5.3 + TailwindCSS/Alpine.js
- 👤 **User Management** - Kompletný user modul s autentifikáciou
- 🔒 **Enterprise Security** - CSRF, Path traversal, CSP protection
- ⚡ **Production Build** - 86% redukcia veľkosti (37MB → 5.8MB)
- 📱 **SEO & Accessibility** - WCAG compliant, optimalizované pre vyhľadávače
- 🛡️ **Apache Ready** - Kompletná .htaccess konfigurácia

## 🚀 Quick Start

```bash
# 1. Inštalácia
composer install

# 2. Inicializácia databáz
php bin/migrate.php

# 3. Spustenie development servera
composer serve                    # http://localhost:8080

# 4. Production build
composer build:production:package # → build/production/
```

## 🎯 Demo stránky

- **`/`** - Domovská stránka s navigáciou
- **`/bootstrap-demo`** - Bootstrap 5.3 theme showcase
- **`/main-demo`** - TailwindCSS + Alpine.js demo
- **`/user/login`** - User authentication systém

## 👤 Default Users

| Username | Password | Roles | Popis |
|----------|----------|-------|-------|
| `admin` | `admin123` | admin, user | Plný administrátorský prístup |
| `user` | `user123` | user | Štandardný používateľ |
| `mark` | `mark123` | mark, user | Mark management prístup |

## 📚 Dokumentácia

### 🇸🇰 Slovenská dokumentácia (odporúčané)
- **[/docs/sk/README.md](sk/README.md)** - Hlavný prehľad (slovensky)
- **[/docs/sk/RYCHLY_START.md](sk/RYCHLY_START.md)** - Rýchly štart
- **[/docs/sk/ARCHITEKTURA.md](sk/ARCHITEKTURA.md)** - Architektúra systému
- **[/docs/sk/KONFIGURACIA.md](sk/KONFIGURACIA.md)** - Konfigurácia
- **[/docs/sk/USER_MODUL.md](sk/USER_MODUL.md)** - User management
- **[/docs/sk/BEZPECNOST.md](sk/BEZPECNOST.md)** - Bezpečnosť
- **[/docs/sk/DEPLOYMENT.md](sk/DEPLOYMENT.md)** - Production deployment
- **[/docs/sk/RIESENIE_PROBLEMOV.md](sk/RIESENIE_PROBLEMOV.md)** - Troubleshooting

### 🇬🇧 English Documentation (reference)
- **[QUICK_START.md](QUICK_START.md)** - Detailný návod na spustenie
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Architektúra a theme systém
- **[USER_MODULE.md](USER_MODULE.md)** - User modul a autentifikácia
- **[API_REFERENCE.md](API_REFERENCE.md)** - API dokumentácia
- **[CONFIGURATION.md](CONFIGURATION.md)** - Konfiguračné možnosti
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Production deployment
- **[SECURITY_GUIDE.md](SECURITY_GUIDE.md)** - Bezpečnostný návod
- **[APACHE_GUIDE.md](APACHE_GUIDE.md)** - Apache konfigurácia
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Riešenie problémov
- **[MAINTENANCE.md](MAINTENANCE.md)** - Údržba a monitoring
- **[ACCESSIBILITY.md](ACCESSIBILITY.md)** - SEO & accessibility guide

### Archive a Štandardy
- **[DOCUMENTATION_STYLE_GUIDE.md](DOCUMENTATION_STYLE_GUIDE.md)** - Štýl guide pre dokumentáciu
- **[/docs/archive/](archive/)** - Technické audity a staršie dokumenty
- **[CHANGELOG.md](CHANGELOG.md)** - História zmien a verzie
- **[TODO.md](TODO.md)** - Plánované vylepšenia

## 🏗️ Architektúra

```
mezzio-minimal/
├── public/                      # Web root
│   ├── themes/                 # Built assets (versioned)
│   └── .htaccess              # Apache security config
├── src/                        # Core application
│   ├── Handler/               # Request handlers
│   └── Helper/                # AssetHelper pre dynamic loading
├── modules/User/              # User management module
│   ├── src/                   # User services & entities
│   └── templates/             # User templates
├── themes/                    # Theme source files
│   ├── bootstrap/             # Bootstrap 5.3 + Vite
│   └── main/                  # TailwindCSS + Alpine.js
├── build/                     # Production builds
│   ├── production/            # Ready-to-deploy (5.8MB)
│   └── releases/              # Versioned archives
└── docs/                      # Dokumentácia
```

## ⚡ Performance

- **86% redukcia veľkosti** - 37MB → 5.8MB production build
- **Versioned assets** - Hash pre long-term cache strategy
- **Gzip compression** - 70-80% redukcia asset veľkosti
- **Optimized vendor** - Odstránené docs, tests, examples

## 🔒 Bezpečnosť

- **Apache .htaccess** - Security headers, directory protection
- **Content Security Policy** - Alpine.js a Bootstrap compatible
- **Path Traversal Protection** - Centralizovaná validácia ciest
- **CSRF Protection** - Tokeny pre všetky formuláre
- **Session Security** - HTTPOnly cookies, secure settings

## 📄 Licencia

MIT License

---

**Status:** ✅ Production Ready v2.0.1
**Posledná aktualizácia:** 2025-07-06
**Build systém:** Vite + Composer optimalizácie
