# 🏗️ Architektúra - Mezzio Minimal

Kompletný prehľad architektúry, theme systému a build procesu.

## 📋 Prehľad Projektu

Minimálna Mezzio aplikácia s pokročilým theme systémom, production build optimalizáciami a kompletnou SEO/accessibility podporou.

### 🎯 Kľúčové Funkcie
- **Theme System**: Bootstrap 5.3 + TailwindCSS + Alpine.js
- **Production Build**: 86% redukcia veľkosti (37MB → 5.8MB)
- **Versioned Assets**: Hash pre long-term cache strategy
- **SEO Optimized**: Meta tags, robots.txt, sitemap.xml
- **Accessibility**: WCAG compliant, proper heading hierarchy
- **Security**: Apache .htaccess, CSP headers, directory protection

## 🏛️ Adresárová Štruktúra

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
├── docs/                       # Dokumentácia
├── build-*.sh                 # Build scripty
└── composer.json               # PHP dependencies
```

## 🎨 Theme System

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

## 🔧 Build System

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

## 🔄 Theme Development Workflow

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

## 🚀 Production Deployment

### Deployment Process
```bash
# 1. Vytvor production build
composer build:production:package

# 2. Upload na server
scp -r build/production/* user@server:/var/www/html/

# 3. Nastav document root
# Apache: DocumentRoot /var/www/html/public

# 4. Skontroluj permissions
chmod -R 755 /var/www/html
chmod -R 644 /var/www/html/config /var/www/html/src
```

### Production Features
- **Optimized autoloader**: 586 classes pre-loaded
- **Versioned assets**: Long-term cache strategy
- **Security headers**: XSS, CSRF, Clickjacking protection
- **Directory protection**: Sensitive files blocked
- **Gzip compression**: Automatic asset compression

## 🔒 Security Architecture

### Directory Protection
- **Root .htaccess**: Redirect na public/, blokuje citlivé adresáre
- **Public .htaccess**: Security headers, caching, URL rewriting
- **Directory protection**: config/, src/, vendor/, themes/ chránené

### Content Security Policy
```apache
Content-Security-Policy: default-src 'self'; 
script-src 'self' 'unsafe-inline' 'unsafe-eval'; 
style-src 'self' 'unsafe-inline'; 
img-src 'self' data: blob: https://picsum.photos; 
font-src 'self'
```

**Poznámky:**
- **`'unsafe-eval'`**: Potrebné pre Alpine.js reactive expressions
- **`blob:`**: Potrebné pre Bootstrap dynamické obrázky
- **`'unsafe-inline'`**: Pre inline štýly a scripty

## 📈 Performance Optimizations

### Asset Optimization
- **Versioned filenames**: `main-D30XL3Ms.css`
- **Long-term caching**: 1 year cache headers
- **Gzip compression**: 70-80% size reduction
- **Minification**: CSS/JS optimized

### Database Optimization
- **SQLite WAL mode**: Better concurrency
- **Prepared statements**: SQL injection protection
- **Connection pooling**: Efficient resource usage

### Server Optimization
- **OPcache**: PHP bytecode caching
- **Apache modules**: mod_deflate, mod_expires
- **HTTP/2**: Modern protocol support

---

## 📚 Súvisiace Dokumenty

### 🚀 Deployment a Production
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Production deployment guide
- **[APACHE_GUIDE.md](APACHE_GUIDE.md)** - Apache konfigurácia a optimalizácie
- **[SECURITY_GUIDE.md](SECURITY_GUIDE.md)** - Bezpečnostné best practices

### ⚙️ Konfigurácia a Development
- **[CONFIGURATION.md](CONFIGURATION.md)** - Konfiguračné možnosti
- **[USER_MODULE.md](USER_MODULE.md)** - User modul dokumentácia
- **[API_REFERENCE.md](API_REFERENCE.md)** - API dokumentácia

### 🔧 Údržba a Support
- **[MAINTENANCE.md](MAINTENANCE.md)** - Údržba a monitoring
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Riešenie problémov
- **[QUICK_START.md](QUICK_START.md)** - Rýchly štart

**Späť na hlavnú:** [README.md](README.md)
