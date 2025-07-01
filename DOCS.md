# Mezzio Minimal - Kompletná Dokumentácia

## 📋 Prehľad Projektu

Minimálna Mezzio aplikácia s pokročilým theme systémom, production build optimalizáciami a kompletnou SEO/accessibility podporou.

### 🎯 Kľúčové Funkcie
- **Theme System**: Bootstrap 5.3 + TailwindCSS + Alpine.js
- **Production Build**: 86% redukcia veľkosti (37MB → 5.8MB)
- **Versioned Assets**: Hash pre long-term cache strategy
- **SEO Optimized**: Meta tags, robots.txt, sitemap.xml
- **Accessibility**: WCAG compliant, proper heading hierarchy
- **Security**: Apache .htaccess, CSP headers, directory protection

---

## 🚀 Quick Start

### Spustenie Development Servera
```bash
composer serve                    # http://localhost:8080
```

### Build Commands
```bash
composer build:dev                # Development build (no hash)
composer build:production:package # Production build → build/production/
composer build:staging           # Staging build → build/staging/
composer build:release           # Release archive → build/releases/
composer clean:build             # Vyčistí build adresáre
```

### Theme Development
```bash
# Bootstrap theme
cd themes/bootstrap
pnpm install
pnpm run dev                      # Development server
pnpm run build                    # Build bez hash
pnpm run build:prod              # Build s hash

# TailwindCSS theme  
cd themes/main
pnpm install
pnpm run dev                      # Development server
pnpm run build                    # Build bez hash
pnpm run build:prod              # Build s hash
```

---

## 🏗️ Architektúra

### Adresárová Štruktúra
```
mezzio-minimal/
├── build/                       # Production builds
│   ├── production/              # Ready-to-deploy (5.8MB)
│   ├── staging/                 # Testing builds
│   └── releases/                # Versioned archives
├── config/                      # Mezzio konfigurácia
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
├── themes/                     # Theme source files
│   ├── bootstrap/              # Bootstrap 5.3 + Vite
│   └── main/                   # TailwindCSS + Alpine.js + Vite
├── build-*.sh                 # Build scripty
└── *.md                       # Dokumentácia
```

### Theme System
- **Nezávislé builds**: Každá téma má vlastný package.json a build
- **Versioned assets**: Hash v názvoch pre cache busting
- **AssetHelper**: Dynamické načítanie cez manifest.json
- **Secure paths**: Len built assets sú verejne dostupné

---

## 🎨 Témy

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

---

## 🔧 Production Build

### Build Process
1. **Copy source files** (exclude build/, node_modules)
2. **Install production dependencies** (--no-dev --optimize-autoloader)
3. **Build themes** s hash pre cache busting
4. **Optimize vendor** (remove docs, tests, examples)
5. **Generate authoritative autoloader** (586 classes)
6. **Set proper permissions** (755/644)
7. **Create build info** s detailmi

### Optimalizácie
- **86% redukcia veľkosti**: 37MB → 5.8MB
- **Vendor cleanup**: Odstránené docs, tests, examples
- **Git removal**: .git adresár (32MB) odstránený
- **Asset versioning**: Hash pre long-term cache
- **Gzip compression**: 70-80% redukcia

### Production Deployment
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

---

## 🔒 Bezpečnosť

### Apache .htaccess
- **Root .htaccess**: Redirect na public/, blokuje citlivé adresáre
- **Public .htaccess**: Security headers, caching, URL rewriting
- **Directory protection**: config/, src/, vendor/, themes/ chránené

### Security Headers
```apache
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
X-Frame-Options: SAMEORIGIN
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self'
```

### Content Security Policy
- **`'unsafe-eval'`**: Potrebné pre Alpine.js reactive expressions
- **`blob:`**: Potrebné pre Bootstrap dynamické obrázky
- **`'unsafe-inline'`**: Pre inline štýly a scripty

---

## 📈 SEO & Accessibility

### SEO Optimalizácie
- **Meta descriptions**: Unique pre každú stránku
- **Title tags**: Descriptive s context
- **Keywords**: Relevantné pre obsah
- **Robots.txt**: Validný s absolútnou sitemap URL
- **XML Sitemap**: Proper structure s priorities

### Accessibility Features
- **Lang attributes**: `lang="sk"` na všetkých stránkach
- **Heading hierarchy**: h1→h2→h3→h4 bez preskakovaných úrovní
- **ARIA labels**: Navigation a interactive elements
- **Color contrast**: Dostatočný contrast ratio
- **Semantic HTML**: Proper markup structure

### Lighthouse Scores
- **Performance**: Optimalizované assets, gzip, cache headers
- **Accessibility**: WCAG compliant, proper structure
- **Best Practices**: Security headers, HTTPS ready
- **SEO**: Meta tags, robots.txt, sitemap

---

## 📚 Dokumentácia

### Dostupné Súbory
- **DOCS.md**: Tento súbor - kompletný prehľad
- **ACCESSIBILITY.md**: SEO a accessibility best practices
- **APACHE_CONFIG.md**: Apache virtual host konfigurácia
- **CSP_OPTIONS.md**: Content Security Policy možnosti
- **HTACCESS_INFO.md**: .htaccess konfigurácia detaily
- **BUILD_INFO.txt**: Detailné build informácie (generované)

### Routes
- **`/`**: Home page s navigáciou
- **`/bootstrap-demo`**: Bootstrap téma demo
- **`/main-demo`**: TailwindCSS + Alpine.js demo

---

## 🛠️ Development

### Pridanie Novej Témy
1. Vytvor adresár `themes/nova-tema/`
2. Pridaj `package.json`, `vite.config.js`
3. Vytvor `src/main.js` a `src/style.css`
4. Aktualizuj build scripty
5. Vytvor handler pre demo stránku

### Debugging
```bash
# Skontroluj server log
composer serve

# Testuj assets
curl -I http://localhost:8080/themes/bootstrap/assets/main-[hash].css

# Validuj robots.txt
curl http://localhost:8080/robots.txt

# Lighthouse audit
npx lighthouse http://localhost:8080 --view
```

### Maintenance
```bash
# Update dependencies
composer update
cd themes/bootstrap && pnpm update
cd themes/main && pnpm update

# Rebuild themes
composer clean:themes
composer build:themes:prod

# Create new release
composer build:release
```

---

## 🎯 Budúce Vylepšenia

### Plánované Features
- [ ] Docker kontajnerizácia
- [ ] CI/CD pipeline (GitHub Actions)
- [ ] Multi-language support
- [ ] Database integration
- [ ] API endpoints
- [ ] User authentication
- [ ] Admin panel

### Performance Optimizations
- [ ] Service Worker pre offline support
- [ ] Critical CSS inlining
- [ ] Image optimization (WebP)
- [ ] CDN integration
- [ ] Database query optimization

---

## 📞 Support

### Troubleshooting
- **500 Error**: Skontroluj permissions, .htaccess syntax
- **Assets 404**: Rebuild themes, skontroluj manifest.json
- **CSP Errors**: Aktualizuj CSP pre nové frameworky
- **Build Fails**: Skontroluj Node.js verziu, pnpm install

### Kontakt
- **Projekt**: Mezzio Minimal Theme System
- **Verzia**: 2.0.0 (Production Ready)
- **Posledný update**: 2025-07-01
- **Status**: ✅ Production Ready

---

*Tento projekt je pripravený na produkčné nasadenie s profesionálnymi optimalizáciami pre výkon, bezpečnosť a SEO.*
