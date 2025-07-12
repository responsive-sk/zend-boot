# Mezzio Minimal - Produkčná Aplikácia

Moderná Mezzio aplikácia s pokročilým theme systémom a production optimalizáciami.

## Hlavné funkcie

- **Dual Theme System** - Bootstrap 5.3 + TailwindCSS/Alpine.js
- **User Management** - Kompletný user modul s autentifikáciou
- **Bezpečnosť** - CSRF, Path traversal, CSP protection
- **Production Build** - 86% redukcia veľkosti (37MB → 5.8MB)
- **SEO & Accessibility** - WCAG compliant
- **Apache Ready** - Kompletná .htaccess konfigurácia

## Rýchly štart

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

## Demo stránky

- **`/`** - Domovská stránka
- **`/bootstrap-demo`** - Bootstrap 5.3 theme
- **`/main-demo`** - TailwindCSS + Alpine.js demo
- **`/user/login`** - User authentication

## Predvolení používatelia

| Username | Password | Roles | Popis |
|----------|----------|-------|-------|
| `admin` | `admin123` | admin, user | Administrátorský prístup |
| `user` | `user123` | user | Štandardný používateľ |
| `mark` | `mark123` | mark, user | Mark management |

## Dokumentácia

### Základné
- **[RYCHLY_START.md](RYCHLY_START.md)** - Detailný návod na spustenie
- **[ARCHITEKTURA.md](ARCHITEKTURA.md)** - Architektúra a theme systém
- **[KONFIGURACIA.md](KONFIGURACIA.md)** - Konfiguračné možnosti

### Používateľské
- **[USER_MODUL.md](USER_MODUL.md)** - User modul a autentifikácia
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Production deployment
- **[BEZPECNOST.md](BEZPECNOST.md)** - Bezpečnostný návod
- **[RIESENIE_PROBLEMOV.md](RIESENIE_PROBLEMOV.md)** - Troubleshooting

### Referenčné (anglicky)
- **[/docs/en/API_REFERENCE.md](../API_REFERENCE.md)** - API dokumentácia
- **[/docs/en/APACHE_GUIDE.md](../APACHE_GUIDE.md)** - Apache konfigurácia
- **[/docs/en/ACCESSIBILITY.md](../ACCESSIBILITY.md)** - SEO & accessibility
- **[/docs/en/MAINTENANCE.md](../MAINTENANCE.md)** - Údržba a monitoring

## Štruktúra projektu

```
mezzio-minimal/
├── public/                      # Web root
│   ├── themes/                 # Built assets
│   └── .htaccess              # Apache security
├── src/                        # Core application
├── modules/User/              # User management
├── themes/                    # Theme source files
│   ├── bootstrap/             # Bootstrap 5.3
│   └── main/                  # TailwindCSS + Alpine.js
├── build/production/          # Production build (5.8MB)
└── docs/sk/                   # Slovenská dokumentácia
```

## Performance

- **86% redukcia veľkosti** - 37MB → 5.8MB
- **Versioned assets** - Hash pre cache strategy
- **Gzip compression** - 70-80% redukcia
- **Optimized vendor** - Bez docs, tests, examples

## Bezpečnosť

- **Apache .htaccess** - Security headers
- **Content Security Policy** - CSP headers
- **Path Traversal Protection** - Validácia ciest
- **CSRF Protection** - Tokeny pre formuláre
- **Session Security** - HTTPOnly cookies

## Príklad kódu s Syntax Highlighting

### PHP Handler

```php
<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Home Page Handler
 */
class HomeHandler implements RequestHandlerInterface
{
    private TemplateRendererInterface $template;

    public function __construct(TemplateRendererInterface $template)
    {
        $this->template = $template;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new HtmlResponse(
            $this->template->render('app::home', [
                'title' => 'Mezzio Minimal',
                'message' => 'Vitajte v Mezzio aplikácii!',
            ])
        );
    }
}
```

### JavaScript konfigurácia

```javascript
// Vite konfigurácia pre theme systém
import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    build: {
        rollupOptions: {
            input: {
                bootstrap: resolve(__dirname, 'themes/bootstrap/main.js'),
                tailwind: resolve(__dirname, 'themes/tailwind/main.js'),
            },
            output: {
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash].[ext]',
            },
        },
    },
    server: {
        proxy: {
            '/api': 'http://localhost:8000',
        },
    },
});
```

### CSS štýly

```css
/* Orbit CMS Documentation Styles */
.docs-container {
    display: grid;
    grid-template-columns: 250px 1fr 200px;
    gap: 2rem;
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.docs-sidebar {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.5rem;
    height: fit-content;
    position: sticky;
    top: 2rem;
}

/* Code blocks with syntax highlighting */
pre[class*="language-"] {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    padding: 1rem;
    overflow-x: auto;
    margin: 1rem 0;
    position: relative;
    line-height: 1.5;
}
```

---

**Status:** Production Ready v2.0.1
**Posledná aktualizácia:** 2025-07-12
**Licencia:** MIT License
