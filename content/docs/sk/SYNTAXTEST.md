# Syntax Highlighting Test

Táto stránka testuje syntax highlighting pre rôzne programovacie jazyky v Orbit CMS dokumentácii.

## PHP Kód

```php
<?php

declare(strict_types=1);

namespace Orbit\Service;

use Orbit\Entity\Content;
use PDO;

/**
 * Content Repository
 * 
 * Správa obsahu v databáze.
 */
class ContentRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?Content
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, cat.name as category_name, cat.color as category_color
            FROM orbit_content c
            LEFT JOIN orbit_categories cat ON c.category_id = cat.id
            WHERE c.id = :id
        ");
        
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        
        if (!is_array($row)) {
            return null;
        }
        
        return $this->hydrate($row);
    }
}
```

## JavaScript Kód

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize search functionality
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    
    async function performSearch(query) {
        try {
            const response = await fetch(`/api/orbit/search?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.results && data.results.length > 0) {
                displayResults(data.results);
            } else {
                searchResults.innerHTML = '<p>Žiadne výsledky nenájdené.</p>';
            }
        } catch (error) {
            console.error('Search error:', error);
            searchResults.innerHTML = '<p>Chyba pri vyhľadávaní.</p>';
        }
    }
    
    function displayResults(results) {
        const html = results.map(result => `
            <div class="search-result">
                <h3><a href="${result.url}">${result.title}</a></h3>
                <p>${result.snippet}</p>
                <small>${result.type} - ${result.updated_at}</small>
            </div>
        `).join('');
        
        searchResults.innerHTML = html;
    }
    
    // Debounced search
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length >= 3) {
            searchTimeout = setTimeout(() => performSearch(query), 300);
        } else {
            searchResults.innerHTML = '';
        }
    });
});
```

## CSS Kód

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

.docs-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.docs-nav li {
    margin-bottom: 0.5rem;
}

.docs-nav a {
    display: block;
    padding: 0.5rem 0.75rem;
    color: #495057;
    text-decoration: none;
    border-radius: 0.25rem;
    transition: all 0.2s ease;
}

.docs-nav a:hover,
.docs-nav a.active {
    background: #007bff;
    color: white;
}

/* Code blocks */
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

code[class*="language-"] {
    font-family: 'Fira Code', 'Monaco', 'Consolas', monospace;
    font-size: 0.875rem;
}

@media (max-width: 1024px) {
    .docs-container {
        grid-template-columns: 1fr;
    }
    
    .docs-sidebar {
        display: none;
    }
}
```

## SQL Kód

```sql
-- Orbit CMS Database Schema
CREATE TABLE orbit_content (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type VARCHAR(50) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    category_id INTEGER,
    published BOOLEAN DEFAULT 0,
    featured BOOLEAN DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES orbit_categories(id)
);

-- Full-text search index
CREATE VIRTUAL TABLE orbit_fts USING fts5(
    title,
    content,
    type,
    slug,
    category_name,
    content_id UNINDEXED
);

-- Insert sample data
INSERT INTO orbit_content (type, slug, title, file_path, published) VALUES
('docs', 'sk/README', 'Úvod do projektu', 'content/docs/sk/README.md', 1),
('docs', 'sk/INSTALL', 'Inštalácia', 'content/docs/sk/INSTALL.md', 1),
('page', 'about', 'O projekte', 'content/pages/about.md', 1);

-- Search query example
SELECT 
    c.title,
    c.slug,
    c.type,
    snippet(orbit_fts, 1, '<mark>', '</mark>', '...', 32) as snippet
FROM orbit_fts 
JOIN orbit_content c ON orbit_fts.content_id = c.id
WHERE orbit_fts MATCH 'mezzio OR orbit'
ORDER BY rank
LIMIT 10;
```

## JSON Konfigurácia

```json
{
    "orbit": {
        "content_path": "content",
        "database": {
            "dsn": "sqlite:data/orbit.db",
            "options": {
                "PDO::ATTR_ERRMODE": "PDO::ERRMODE_EXCEPTION",
                "PDO::ATTR_DEFAULT_FETCH_MODE": "PDO::FETCH_ASSOC"
            }
        },
        "search": {
            "enabled": true,
            "index_on_save": true,
            "highlight_snippets": true,
            "max_results": 50
        },
        "drivers": [
            "Orbit\\Service\\FileDriver\\MarkdownDriver",
            "Orbit\\Service\\FileDriver\\JsonDriver"
        ]
    },
    "mezzio-authorization-rbac": {
        "roles": {
            "user": [],
            "editor": ["user"],
            "mark": ["editor"],
            "supermark": ["mark"]
        },
        "permissions": {
            "user": ["view.public"],
            "editor": ["edit.content"],
            "mark": ["manage.orbit", "access.mark"],
            "supermark": ["manage.users", "system.admin"]
        }
    }
}
```

## Bash Script

```bash
#!/bin/bash

# Orbit CMS Deployment Script
set -e

echo "🚀 Deploying Orbit CMS..."

# Update dependencies
echo "📦 Updating dependencies..."
composer install --no-dev --optimize-autoloader

# Clear cache
echo "🧹 Clearing cache..."
rm -rf data/cache/*

# Run database migrations
echo "🗄️ Running database migrations..."
php bin/orbit-db-helper.php migrate

# Reindex content
echo "🔍 Reindexing content..."
php bin/orbit-db-helper.php reindex

# Set permissions
echo "🔒 Setting permissions..."
chmod -R 755 public/
chmod -R 777 data/
chmod -R 777 logs/

# Build assets
echo "🎨 Building assets..."
npm run build

echo "✅ Deployment completed successfully!"
echo "🌐 Application is ready at: https://your-domain.com"
```

## YAML Konfigurácia

```yaml
# Orbit CMS Configuration
orbit:
  content_path: content
  
  database:
    dsn: "sqlite:data/orbit.db"
    options:
      PDO::ATTR_ERRMODE: PDO::ERRMODE_EXCEPTION
      PDO::ATTR_DEFAULT_FETCH_MODE: PDO::FETCH_ASSOC
  
  search:
    enabled: true
    index_on_save: true
    highlight_snippets: true
    max_results: 50
  
  drivers:
    - "Orbit\\Service\\FileDriver\\MarkdownDriver"
    - "Orbit\\Service\\FileDriver\\JsonDriver"

# Mezzio Authorization
mezzio-authorization-rbac:
  roles:
    user: []
    editor: [user]
    mark: [editor]
    supermark: [mark]
  
  permissions:
    user: [view.public]
    editor: [edit.content]
    mark: [manage.orbit, access.mark]
    supermark: [manage.users, system.admin]
```

## Inline kód

Tu je príklad `inline kódu` v texte. Môžete použiť `$variable` alebo `function()` priamo v odstavci.

Taktiež môžete použiť **bold `code`** alebo *italic `code`* kombinované s inline kódom.

## Záver

Syntax highlighting je teraz plne funkčný v Orbit CMS dokumentácii s podporou pre:

- ✅ PHP, JavaScript, CSS, SQL, JSON, Bash, YAML
- ✅ Automatická detekcia jazyka
- ✅ Copy-to-clipboard funkcionalita
- ✅ Line numbers (voliteľné)
- ✅ Responsive dizajn
- ✅ Dark/light témy
- ✅ Language labels na code blocks
