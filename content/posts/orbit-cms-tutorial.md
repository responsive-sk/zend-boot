---
title: "Orbit CMS - Kompletný tutoriál"
slug: "orbit-cms-tutorial"
excerpt: "Naučte sa používať Orbit CMS od základov. Flat-file CMS s Markdown podporou a moderným dizajnom."
content_type: "post"
status: "published"
featured: true
published_at: "2025-01-12T16:45:00Z"
created_at: "2025-01-12T16:45:00Z"
updated_at: "2025-01-12T16:45:00Z"
category: "Tutorial"
tags: ["Orbit CMS", "Tutorial", "Flat-file CMS", "Markdown"]
image: "/themes/main/assets/logo-CoNUu8jz.svg"
author: "Orbit CMS Team"
---

# Orbit CMS - Kompletný tutoriál pre začiatočníkov

Orbit CMS je moderný flat-file CMS systém postavený na PHP a Mezzio frameworku. Naučte sa ho používať!

![Orbit CMS](/themes/main/assets/logo-CoNUu8jz.svg)

## Čo je Orbit CMS?

Orbit CMS je **flat-file content management systém**, ktorý:

- ✅ **Nepotrebuje databázu** pre obsah
- ✅ **Používa Markdown** pre písanie
- ✅ **Má moderný dizajn** s Tailwind CSS
- ✅ **Podporuje kategórie a tagy**
- ✅ **Je rýchly a bezpečný**

## Inštalácia

### 1. Požiadavky
```bash
# PHP 8.1+
php --version

# Composer
composer --version

# Node.js (pre assets)
node --version
```

### 2. Stiahnutie
```bash
git clone https://github.com/your-repo/orbit-cms.git
cd orbit-cms
composer install
npm install
```

### 3. Konfigurácia
```bash
# Kopírovanie konfigurácie
cp config/autoload/local.php.dist config/autoload/local.php

# Build assets
npm run build
```

## Vytvorenie obsahu

### Blog posty
Vytvorte súbor `content/posts/moj-prvy-post.md`:

```markdown
---
title: "Môj prvý blog post"
slug: "moj-prvy-post"
excerpt: "Toto je môj prvý príspevok v Orbit CMS"
content_type: "post"
status: "published"
published_at: "2025-01-12T10:00:00Z"
category: "Všeobecné"
tags: ["Začiatok", "Blog"]
---

# Vitajte v Orbit CMS!

Toto je môj prvý príspevok. **Markdown** je úžasný!

## Funkcie

- Jednoduché písanie
- Syntax highlighting
- Obrázky a linky
- Tabuľky

## Kód

```php
<?php
echo "Hello, Orbit CMS!";
```

Užívajte si písanie!
```

### Stránky
Vytvorte súbor `content/pages/o-nas.md`:

```markdown
---
title: "O nás"
slug: "o-nas"
content_type: "page"
status: "published"
---

# O našej spoločnosti

Sme tím vývojárov, ktorí milujú **moderné technológie**.
```

## Správa obsahu

### Admin rozhranie
Navštívte `/mark/orbit/content` pre správu obsahu:

1. **Zoznam príspevkov** - prehľad všetkého obsahu
2. **Editácia** - úprava existujúceho obsahu
3. **Vytváranie** - nový obsah
4. **Kategórie a tagy** - organizácia

### Súborová štruktúra
```
content/
├── posts/          # Blog príspevky
├── pages/          # Statické stránky
├── docs/           # Dokumentácia
└── uploads/        # Nahrané súbory
```

## Témy a dizajn

### Dostupné témy
- **Bootstrap téma** - `/blog`
- **Tailwind téma** - `/blog-tailwind`
- **Home téma** - `/`

### Prispôsobenie
```php
// V template súbore
$this->layout('layout::home', [
    'title' => 'Môj blog'
]);
```

## Pokročilé funkcie

### Vyhľadávanie
```php
// API endpoint
GET /api/search?q=orbit&type=post
```

### Kategórie a tagy
```yaml
# V YAML hlavičke
category: "Technology"
tags: ["PHP", "CMS", "Web Development"]
```

### Obrázky
```markdown
![Alt text](/themes/main/assets/image.jpg)
```

## Deployment

### Production build
```bash
# Optimalizovaný build
./bin/build-production.sh shared-hosting-minimal

# Upload na server
scp -r build/shared-hosting-minimal/* user@server:/var/www/
```

### Konfigurácia servera
```apache
# .htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

## Tipy a triky

### 1. Markdown syntax
```markdown
# H1 nadpis
## H2 nadpis

**Tučný text**
*Kurzíva*

- Zoznam
- Položka 2

[Link](https://example.com)
```

### 2. Kód bloky
```php
<?php
// PHP kód s syntax highlighting
function hello($name) {
    return "Hello, {$name}!";
}
```

### 3. Tabuľky
```markdown
| Stĺpec 1 | Stĺpec 2 |
|----------|----------|
| Hodnota 1| Hodnota 2|
```

## Záver

Orbit CMS je mocný a jednoduchý nástroj pre správu obsahu. Začnite písať už dnes!

### Ďalšie kroky
1. Prečítajte si [dokumentáciu](/docs)
2. Pozrite si [príklady](/examples)
3. Pripojte sa k [komunite](/community)

---

*Máte otázky? Kontaktujte nás na support@orbit-cms.com*
