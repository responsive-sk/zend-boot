# Architektúra - Mezzio Minimal

Prehľad architektúry, theme systému a build procesu.

## Prehľad Projektu

Minimálna Mezzio aplikácia s pokročilým theme systémom a production optimalizáciami.

### Kľúčové Funkcie
- **Theme System**: Bootstrap 5.3 + TailwindCSS + Alpine.js
- **Production Build**: 86% redukcia veľkosti (37MB → 5.8MB)
- **Versioned Assets**: Hash pre long-term cache strategy
- **SEO Optimized**: Meta tags, robots.txt, sitemap.xml
- **Security**: Apache .htaccess, CSP headers, directory protection

## Štruktúra Projektu

```
mezzio-minimal/
├── build/                       # Production builds
│   ├── production/              # Ready-to-deploy (5.8MB)
│   ├── staging/                 # Testing builds
│   └── releases/                # Versioned archives
├── config/                      # Mezzio konfigurácia
│   └── autoload/               # Auto-loaded configs
├── public/                      # Web root
│   ├── .htaccess               # Apache konfigurácia
│   ├── robots.txt              # SEO crawling rules
│   ├── sitemap.xml             # XML sitemap
│   └── themes/                 # Built assets (SECURE)
│       ├── bootstrap/assets/   # Bootstrap CSS/JS s hash
│       └── main/assets/        # TailwindCSS/Alpine.js s hash
├── src/                        # PHP aplikačný kód
│   ├── Handler/                # Route handlers
│   └── Helper/                 # AssetHelper pre dynamic loading
├── modules/User/               # User management module
│   ├── src/                    # User services & entities
│   └── templates/              # User module templates
├── themes/                     # Theme source files
│   ├── bootstrap/              # Bootstrap 5.3 + Vite
│   └── main/                   # TailwindCSS + Alpine.js + Vite
├── data/                       # SQLite databases
└── docs/sk/                    # Slovenská dokumentácia
```

## Theme System

### Architektúra Theme Systému
- **Nezávislé builds**: Každá téma má vlastný package.json a build
- **Versioned assets**: Hash v názvoch pre cache busting
- **AssetHelper**: Dynamické načítanie cez manifest.json
- **Secure paths**: Len built assets sú verejne dostupné

### Bootstrap Theme (`/bootstrap-demo`)
- **Framework**: Bootstrap 5.3.7 + Popper.js
- **Build**: Vite s production optimalizáciou
- **Assets**: `main-D30XL3Ms.css` (231KB → 31KB gzipped)
- **Features**: Responzívne komponenty, utility classes

### TailwindCSS Theme (`/main-demo`)
- **Framework**: TailwindCSS 3.4 + Alpine.js 3.14
- **Build**: Vite s PostCSS a Autoprefixer
- **Assets**: `main-D3jsrGqr.css` (9.9KB → 2.6KB gzipped)
- **Features**: Utility-first CSS, reactive komponenty

### Asset Loading
```php
// V handleroch
$assetHelper = $container->get(AssetHelper::class);
$cssUrl = $assetHelper->css('bootstrap');     // Automaticky hash
$jsUrl = $assetHelper->js('bootstrap');       // Z manifest.json
$themeInfo = $assetHelper->getThemeInfo('bootstrap');
```

## Build System

### Build Process
1. **Copy source files** (exclude build/, node_modules)
2. **Install production dependencies** (--no-dev --optimize-autoloader)
3. **Build themes** s hash pre cache busting
4. **Optimize vendor** (remove docs, tests, examples)
5. **Generate authoritative autoloader** (586 classes)
6. **Set proper permissions** (755/644)
7. **Create build info** s detailmi

### Build Commands
```bash
# Development
composer build:dev                # Development build (no hash)
composer serve                    # Development server

# Production
composer build:production:package # Production build → build/production/
composer build:staging           # Staging build → build/staging/
composer build:release           # Release archive → build/releases/

# Maintenance
composer clean:build             # Vyčistí build adresáre
composer clean:themes            # Vyčistí theme assets
```

### Optimalizácie
- **86% redukcia veľkosti**: 37MB → 5.8MB
- **Vendor cleanup**: Odstránené docs, tests, examples
- **Git removal**: .git adresár (32MB) odstránený
- **Asset versioning**: Hash pre long-term cache
- **Gzip compression**: 70-80% redukcia

## Theme Development Workflow

### 1. Development Mode
```bash
# Bootstrap theme
cd themes/bootstrap
pnpm install
pnpm run dev                      # Development server

# TailwindCSS theme  
cd themes/main
pnpm install
pnpm run dev                      # Development server
```

### 2. Production Build
```bash
# Build jednotlivé témy
cd themes/bootstrap && pnpm run build:prod
cd themes/main && pnpm run build:prod

# Alebo všetky naraz
composer build:themes:prod
```

### 3. Asset Manifest
Každá téma generuje `manifest.json`:
```json
{
  "main.css": {
    "file": "main-D30XL3Ms.css",
    "src": "src/style.css"
  },
  "main.js": {
    "file": "main-Df2FmC7f.js",
    "src": "src/main.js"
  }
}
```

## Bezpečnosť

### Apache .htaccess
- **Directory protection**: Blokuje prístup k citlivým adresárom
- **Security headers**: X-Frame-Options, X-Content-Type-Options
- **Content Security Policy**: Alpine.js a Bootstrap compatible
- **Gzip compression**: Automatická kompresia assets

### Path Traversal Protection
- **Centralizovaná validácia**: Všetky cesty cez AssetHelper
- **Whitelist approach**: Len povolené theme adresáre
- **Sanitization**: Odstránenie nebezpečných znakov

## Performance Metriky

- **Development build**: 37MB (s node_modules, .git)
- **Production build**: 5.8MB (86% redukcia)
- **Vendor size**: 4.2MB → 2.1MB (optimalizované)
- **Theme assets**: Gzipped 70-80% redukcia
- **Autoloader**: 586 classes optimalizované

---

**Ďalšie informácie:**
- [RYCHLY_START.md](RYCHLY_START.md) - Návod na spustenie
- [KONFIGURACIA.md](KONFIGURACIA.md) - Konfiguračné možnosti
- [USER_MODUL.md](USER_MODUL.md) - User management
- [/docs/en/API_REFERENCE.md](../API_REFERENCE.md) - API dokumentácia
