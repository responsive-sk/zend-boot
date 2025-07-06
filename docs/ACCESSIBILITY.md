# ♿ Accessibility & SEO Guide

Kompletný návod na accessibility a SEO optimalizácie v Mezzio Minimal aplikácii.

## ✅ Implementované Accessibility Funkcie

### 1. Sémantická HTML Štruktúra
- ✅ Proper heading hierarchy (h1 → h2 → h3 → h4)
- ✅ `lang` attribute on `<html>` element
- ✅ Navigation with `role="navigation"` and `aria-label`
- ✅ Descriptive `aria-label` attributes on links

### 2. SEO Optimalizácia
- ✅ Meta descriptions on all pages
- ✅ Proper page titles with context
- ✅ Keywords meta tags
- ✅ Author meta tags
- ✅ Valid robots.txt file
- ✅ XML sitemap

### 3. Farebný Kontrast
- ✅ Improved color contrast ratios
- ✅ Dark text on light backgrounds
- ✅ Sufficient contrast for links and buttons

### 4. Content Structure
- ✅ Logical heading order without skipping levels
- ✅ Descriptive link text
- ✅ Proper navigation structure

## Heading Hierarchy

### Correct Structure
```
h1 - Page Title
├── h2 - Main Section
│   ├── h3 - Subsection
│   │   └── h4 - Sub-subsection
│   └── h3 - Another Subsection
└── h2 - Another Main Section
```

### Current Implementation
- **Home Page**: h1 (Page Title)
- **Bootstrap Demo**: h1 (Nav) → h2 (Main Content) → h3 (Sections)
- **Main Demo**: h1 (Nav) → h2 (Main Content) → h3 (Sections) → h4 (Sub-sections)

## SEO Files

### robots.txt
- Allows crawling of main content
- Allows theme assets
- Disallows sensitive directories
- References sitemap

### sitemap.xml
- Lists all public pages
- Includes last modification dates
- Sets priority levels
- Follows XML sitemap protocol

## Testing

### Accessibility Testing
```bash
# Use tools like:
- Lighthouse accessibility audit
- axe-core browser extension
- WAVE Web Accessibility Evaluator
```

### SEO Testing
```bash
# Check:
- Google Search Console
- Lighthouse SEO audit
- Meta tag validators
```

## Future Improvements

### Potential Enhancements
- [ ] Skip navigation links
- [ ] Focus management for interactive elements
- [ ] ARIA landmarks
- [ ] Alt text for images (when added)
- [ ] Form labels (when forms are added)
- [ ] Keyboard navigation testing

### Advanced SEO
- [ ] Open Graph meta tags
- [ ] Twitter Card meta tags
- [ ] JSON-LD structured data
- [ ] Canonical URLs
- [ ] Hreflang attributes (for multi-language)

---

## 📚 Súvisiace Dokumenty

### 🏗️ Development a Architecture
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Theme systém a SEO architektúra
- **[USER_MODULE.md](USER_MODULE.md)** - User accessibility features
- **[API_REFERENCE.md](API_REFERENCE.md)** - Accessibility API

### 🚀 Production a Deployment
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - SEO production setup
- **[APACHE_GUIDE.md](APACHE_GUIDE.md)** - SEO headers konfigurácia
- **[SECURITY_GUIDE.md](SECURITY_GUIDE.md)** - Security vs accessibility balance

### 🔧 Konfigurácia a Support
- **[CONFIGURATION.md](CONFIGURATION.md)** - SEO konfiguračné možnosti
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - SEO a accessibility problémy
- **[MAINTENANCE.md](MAINTENANCE.md)** - SEO monitoring

**Späť na hlavnú:** [README.md](README.md)
