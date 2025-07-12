---
title: "Integrácia Orbit CMS do Mezzio Minimal"
slug: "orbit-cms-integration"
description: "Ako sme integrovali Orbit CMS pre správu obsahu"
published: true
featured: true
created_at: "2025-01-12"
updated_at: "2025-01-12"
published_at: "2025-01-12"
tags: ["orbit", "cms", "mezzio", "php"]
author: "Development Team"
meta:
  keywords: "orbit cms, mezzio, flat file cms, php"
  image: "/media/images/orbit-integration.jpg"
---

# Integrácia Orbit CMS do Mezzio Minimal

Dnes sme úspešne integrovali **Orbit CMS** do nášho Mezzio Minimal projektu. Orbit je flat-file CMS s podporou SQLite, ktorý nám umožňuje jednoducho spravovať obsah.

## Prečo Orbit CMS?

- **Flat-file storage** - Obsah v Markdown súboroch
- **SQLite podpora** - Pre vyhľadávanie a metadata
- **YAML front-matter** - Štruktúrované metadata
- **Multi-format** - Markdown, JSON, YAML
- **Version control friendly** - Obsah v git repository

## Implementácia

### 1. Štruktúra content adresára

```
content/
├── docs/          # Dokumentácia
├── pages/         # Statické stránky  
├── posts/         # Blog články
├── media/         # Médiá súbory
└── templates/     # Content templates
```

### 2. SQLite databáza

Vytvorili sme `data/orbit.db` pre:
- **Metadata** článkov a stránok
- **Full-text search** s FTS5
- **Tags a kategórie**
- **Search index**

### 3. Mark integrácia

Orbit CMS je plne integrovaný s existujúcim **Mark modulom**:
- `/mark/orbit` - Content dashboard
- `/mark/orbit/editor` - Markdown editor
- `/mark/orbit/media` - Media manager

## Výhody

1. **Jednoduchá správa** - Markdown súbory
2. **Rýchle vyhľadávanie** - SQLite FTS5
3. **SEO friendly** - Proper URLs a meta tags
4. **Version control** - Obsah v git
5. **Multi-language** - Podpora SK/EN

## Ďalšie kroky

- **Public dokumentácia** na `/docs/`
- **Search API** pre frontend
- **Media upload** cez mark rozhranie
- **Content templates** pre rýchle vytvorenie

Orbit CMS nám umožní ľahko spravovať obsah a zároveň zachová flexibilitu flat-file prístupu.
