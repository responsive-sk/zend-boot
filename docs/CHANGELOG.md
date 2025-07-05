# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-07-01

### 🚀 Added - Production Build System
- Ultra-optimized build scripts (`build-production.sh`, `build-to-directory.sh`, `build-dev.sh`)
- Build directory structure (`build/production/`, `build/staging/`, `build/releases/`)
- 86% size reduction (37MB → 5.8MB) by removing .git and optimizing vendor
- Authoritative autoloader with 586 optimized classes
- Versioned assets with hash for long-term cache strategy
- Asset versioning: `main-D30XL3Ms.css`, `main-Df2FmC7f.js`, etc.

### 🎨 Added - Theme System Enhancements
- `AssetHelper` class for dynamic asset loading with manifest support
- Production builds with hash for cache busting
- Alpine.js + Bootstrap compatible CSP with `unsafe-eval` and `blob:` support
- Independent theme builds with separate package.json files
- Vite configuration for both development and production modes

### 🔒 Added - Security & Apache Configuration
- Comprehensive `.htaccess` files for root and public directories
- Security headers: XSS, CSRF, Clickjacking protection
- Content Security Policy optimized for Alpine.js and Bootstrap
- Directory protection for config/, src/, vendor/, themes/
- Proper MIME types and gzip compression configuration
- Gzip compression achieving 70-80% asset size reduction

### 📈 Added - SEO & Accessibility Optimizations
- Meta descriptions, keywords, and author tags on all pages
- Proper heading hierarchy (h1→h2→h3→h4) without skipping levels
- Lang attributes (`lang="sk"`) and semantic HTML structure
- Valid `robots.txt` with absolute sitemap URL
- XML sitemap (`sitemap.xml`) with proper structure and priorities
- Improved color contrast ratios for accessibility compliance
- Navigation with ARIA labels and semantic markup
- Favicon support

### ⚡ Added - Performance Optimizations
- Long-term cache headers (1 year for versioned assets)
- Gzip compression for all text-based files
- Optimized vendor directory (removed docs, tests, examples)
- Production-ready permissions and file structure
- Asset versioning for automatic cache busting

### 📚 Added - Documentation
- `DOCS.md` - Complete project documentation and quick start guide
- `ACCESSIBILITY.md` - Complete accessibility and SEO guide
- `APACHE_CONFIG.md` - Apache virtual host configuration
- `CSP_OPTIONS.md` - Content Security Policy options and trade-offs
- `HTACCESS_INFO.md` - .htaccess configuration details
- `BUILD_INFO.txt` - Detailed build information (auto-generated)
- `CHANGELOG.md` - This changelog file

### 🛠️ Added - Build Commands
- `composer build:dev` - Development build (no hash)
- `composer build:production:package` - Production build to build/production/
- `composer build:staging` - Staging build for testing
- `composer build:release` - Release archive with timestamp
- `composer clean:build` - Clean build directories
- `composer clean:themes` - Clean theme assets

### 🔧 Changed
- Updated all handlers to use `AssetHelper` for dynamic asset loading
- Enhanced Bootstrap demo with improved color contrast
- Updated CSP to support both Alpine.js and Bootstrap requirements
- Improved error handling and fallbacks in asset loading
- Updated package.json files with production build scripts

### 🗑️ Removed
- Development configuration files (`phpcs.xml`, `phpstan.neon`, `rector.php`)
- Lock files (`pnpm-lock.yaml`) from git tracking
- Problematic `.htaccess` files that blocked PHP built-in server
- Unnecessary development dependencies from production builds

### 🐛 Fixed
- Robots.txt validation error (invalid sitemap URL)
- Bootstrap color contrast issues for accessibility
- Heading hierarchy problems in demo pages
- CSP blocking Alpine.js reactive expressions
- CSP blocking Bootstrap blob: URLs
- Permission issues in production builds
- Asset loading with proper hash support

---

## [1.0.0] - 2025-07-01

### 🎨 Added - Theme System
- Bootstrap 5.3 theme with Vite build system
- TailwindCSS + Alpine.js theme with Vite build system
- Secure asset management - only built files exposed
- Independent theme builds with separate package.json
- Demo handlers for each theme with responsive designs
- PHP built-in server handles static files automatically

### 🏗️ Added - Core Application
- Minimal Mezzio application structure
- Route handlers: `HomeHandler`, `BootstrapDemoHandler`, `MainDemoHandler`
- Dependency injection configuration
- Basic composer.json with required dependencies

### 🔒 Added - Security Features
- No node_modules exposed publicly
- Only optimized built assets served
- Build process ensures clean separation
- Secure theme architecture using proper file structure

### 📁 Added - Project Structure
```
themes/
├── bootstrap/          # Bootstrap 5.3 + Popper.js
├── main/              # TailwindCSS + Alpine.js
└── public/themes/     # Built assets only (secure)
```

### 🛠️ Added - Routes
- `/` - Home page
- `/bootstrap-demo` - Bootstrap theme demo
- `/main-demo` - TailwindCSS + Alpine.js demo

---

## [0.1.0] - 2025-07-01

### 🎯 Added - Initial Setup
- Initial Mezzio minimal application
- Basic project structure
- Git repository initialization
- README.md with basic information

---

## Upcoming Features

### 🔮 Planned for v2.1.0
- [ ] Docker containerization
- [ ] CI/CD pipeline (GitHub Actions)
- [ ] Multi-language support
- [ ] Database integration
- [ ] API endpoints

### 🔮 Planned for v3.0.0
- [ ] User authentication system
- [ ] Admin panel
- [ ] Service Worker for offline support
- [ ] Critical CSS inlining
- [ ] Image optimization (WebP)
- [ ] CDN integration

---

## Migration Guide

### From v1.0.0 to v2.0.0

1. **Update build process**:
   ```bash
   # Old way
   cd themes/bootstrap && npm run build
   
   # New way
   composer build:production:package
   ```

2. **Update asset references**:
   ```php
   // Old way
   <link href="/themes/bootstrap/assets/main.css" rel="stylesheet">
   
   // New way
   $cssUrl = $assetHelper->css('bootstrap');
   <link href="<?= $cssUrl ?>" rel="stylesheet">
   ```

3. **Update deployment**:
   ```bash
   # Deploy from build directory
   rsync -av build/production/ user@server:/var/www/html/
   ```

---

*For more information, see [DOCS.md](DOCS.md) for complete documentation.*
