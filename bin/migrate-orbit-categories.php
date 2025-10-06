#!/usr/bin/env php
<?php
/**
 * Orbit CMS Categories Migration
 * 
 * Pridá categories systém do existujúcej Orbit databázy.
 */

declare(strict_types=1);

// Load paths configuration
$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

// Get paths configuration
$paths = require $rootDir . '/config/paths.php';
$dbPath = $paths->getPath($paths->base(), $paths->get('orbit_db'));
$dataDir = dirname($dbPath);

// Make paths global for functions
global $paths;

if (!file_exists($dbPath)) {
    echo "❌ Orbit databáza neexistuje. Spusti najprv: php bin/migrate-orbit-db.php\n";
    exit(1);
}

echo "📂 Orbit CMS Categories Migration\n";
echo "==================================\n\n";

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔗 Pripojenie k databáze: $dbPath\n\n";
    
    // Skontroluj, či categories už existujú
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'orbit_categories%'")->fetchAll();
    
    if (!empty($tables)) {
        echo "⚠️  Categories tabuľky už existujú. Preskakujem migráciu.\n";
        exit(0);
    }
    
    // Vytvor categories tabuľky
    createCategoriesTables($pdo);
    
    // Pridaj category_id do orbit_content
    addCategoryToContent($pdo);
    
    // Vytvor indexy
    createCategoriesIndexes($pdo);
    
    // Naplň základné kategórie
    seedCategories($pdo);
    
    echo "\n🎉 Categories systém úspešne pridaný!\n";
    echo "====================================\n";
    echo "📊 Nové tabuľky: orbit_categories, orbit_category_hierarchy\n";
    echo "🔧 Aktualizovaná: orbit_content (pridaný category_id)\n";
    echo "📂 Vytvorených 8 základných kategórií\n\n";
    
} catch (PDOException $e) {
    echo "❌ Chyba pri migrácii: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Vytvorí tabuľky pre categories
 */
function createCategoriesTables(PDO $pdo): void
{
    echo "📋 Vytváram categories tabuľky...\n";
    
    // Hlavná tabuľka pre kategórie
    $pdo->exec("
        CREATE TABLE orbit_categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            parent_id INTEGER NULL,
            color VARCHAR(7) DEFAULT '#6b7280',
            icon VARCHAR(50) DEFAULT 'folder',
            sort_order INTEGER DEFAULT 0,
            is_active BOOLEAN DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES orbit_categories(id) ON DELETE SET NULL
        )
    ");
    echo "   ✅ orbit_categories\n";
    
    // Materialized path pre hierarchiu (pre rýchle queries)
    $pdo->exec("
        CREATE TABLE orbit_category_hierarchy (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER NOT NULL,
            ancestor_id INTEGER NOT NULL,
            depth INTEGER NOT NULL DEFAULT 0,
            path VARCHAR(500) NOT NULL,
            FOREIGN KEY (category_id) REFERENCES orbit_categories(id) ON DELETE CASCADE,
            FOREIGN KEY (ancestor_id) REFERENCES orbit_categories(id) ON DELETE CASCADE,
            UNIQUE(category_id, ancestor_id)
        )
    ");
    echo "   ✅ orbit_category_hierarchy\n";
}

/**
 * Pridá category_id do orbit_content tabuľky
 */
function addCategoryToContent(PDO $pdo): void
{
    echo "\n🔄 Aktualizujem orbit_content tabuľku...\n";
    
    // Pridaj category_id stĺpec
    $pdo->exec("
        ALTER TABLE orbit_content 
        ADD COLUMN category_id INTEGER NULL 
        REFERENCES orbit_categories(id) ON DELETE SET NULL
    ");
    echo "   ✅ Pridaný category_id stĺpec\n";
}

/**
 * Vytvorí indexy pre categories
 */
function createCategoriesIndexes(PDO $pdo): void
{
    echo "\n🔍 Vytváram categories indexy...\n";
    
    $indexes = [
        "CREATE INDEX idx_categories_slug ON orbit_categories(slug)",
        "CREATE INDEX idx_categories_parent ON orbit_categories(parent_id)",
        "CREATE INDEX idx_categories_active ON orbit_categories(is_active)",
        "CREATE INDEX idx_categories_sort ON orbit_categories(sort_order)",
        "CREATE INDEX idx_content_category ON orbit_content(category_id)",
        "CREATE INDEX idx_hierarchy_category ON orbit_category_hierarchy(category_id)",
        "CREATE INDEX idx_hierarchy_ancestor ON orbit_category_hierarchy(ancestor_id)",
        "CREATE INDEX idx_hierarchy_depth ON orbit_category_hierarchy(depth)",
    ];
    
    foreach ($indexes as $index) {
        $pdo->exec($index);
        echo "   ✅ " . substr($index, strpos($index, 'idx_')) . "\n";
    }
}

/**
 * Naplní základné kategórie
 */
function seedCategories(PDO $pdo): void
{
    echo "\n🌱 Vytváram základné kategórie...\n";
    
    // Základné kategórie s hierarchiou
    $categories = [
        // Root kategórie
        ['name' => 'Dokumentácia', 'slug' => 'dokumentacia', 'parent_id' => null, 'color' => '#10b981', 'icon' => 'book', 'sort_order' => 1],
        ['name' => 'Blog', 'slug' => 'blog', 'parent_id' => null, 'color' => '#3b82f6', 'icon' => 'edit', 'sort_order' => 2],
        ['name' => 'Stránky', 'slug' => 'stranky', 'parent_id' => null, 'color' => '#8b5cf6', 'icon' => 'file', 'sort_order' => 3],
        ['name' => 'Tutoriály', 'slug' => 'tutorialy', 'parent_id' => null, 'color' => '#f59e0b', 'icon' => 'academic-cap', 'sort_order' => 4],
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO orbit_categories (name, slug, parent_id, color, icon, sort_order) 
        VALUES (:name, :slug, :parent_id, :color, :icon, :sort_order)
    ");
    
    $categoryIds = [];
    
    // Vytvor root kategórie
    foreach ($categories as $category) {
        $stmt->execute($category);
        $categoryIds[$category['slug']] = $pdo->lastInsertId();
        echo "   ✅ {$category['name']} (root)\n";
    }
    
    // Sub-kategórie pre dokumentáciu
    $subCategories = [
        ['name' => 'Slovensky', 'slug' => 'dokumentacia-sk', 'parent_id' => $categoryIds['dokumentacia'], 'color' => '#059669', 'icon' => 'flag', 'sort_order' => 1],
        ['name' => 'English', 'slug' => 'dokumentacia-en', 'parent_id' => $categoryIds['dokumentacia'], 'color' => '#0d9488', 'icon' => 'flag', 'sort_order' => 2],
        
        // Sub-kategórie pre blog
        ['name' => 'Novinky', 'slug' => 'blog-novinky', 'parent_id' => $categoryIds['blog'], 'color' => '#2563eb', 'icon' => 'newspaper', 'sort_order' => 1],
        ['name' => 'Technické články', 'slug' => 'blog-technicke', 'parent_id' => $categoryIds['blog'], 'color' => '#1d4ed8', 'icon' => 'code', 'sort_order' => 2],
    ];
    
    foreach ($subCategories as $category) {
        $stmt->execute($category);
        $categoryIds[$category['slug']] = $pdo->lastInsertId();
        echo "   ✅ {$category['name']} (sub)\n";
    }
    
    // Vytvor hierarchy paths
    buildCategoryHierarchy($pdo);
    
    // Priradí existujúci content do kategórií
    assignContentToCategories($pdo, $categoryIds);
}

/**
 * Vytvorí hierarchy paths pre rýchle queries
 */
function buildCategoryHierarchy(PDO $pdo): void
{
    echo "\n🌳 Vytváram category hierarchy...\n";
    
    // Vyčisti existujúcu hierarchiu
    $pdo->exec("DELETE FROM orbit_category_hierarchy");
    
    // Získaj všetky kategórie
    $categories = $pdo->query("
        SELECT id, name, parent_id, slug 
        FROM orbit_categories 
        ORDER BY sort_order, name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("
        INSERT INTO orbit_category_hierarchy (category_id, ancestor_id, depth, path)
        VALUES (:category_id, :ancestor_id, :depth, :path)
    ");
    
    foreach ($categories as $category) {
        $path = buildPath($category, $categories);
        $ancestors = explode('/', trim($path, '/'));
        
        // Pridaj self-reference (depth 0)
        $stmt->execute([
            'category_id' => $category['id'],
            'ancestor_id' => $category['id'],
            'depth' => 0,
            'path' => $path
        ]);
        
        // Pridaj ancestors
        $depth = 1;
        $parentId = $category['parent_id'];
        
        while ($parentId) {
            $stmt->execute([
                'category_id' => $category['id'],
                'ancestor_id' => $parentId,
                'depth' => $depth,
                'path' => $path
            ]);
            
            // Nájdi parent
            $parent = array_filter($categories, fn($c) => $c['id'] == $parentId);
            $parent = reset($parent);
            
            $parentId = $parent ? $parent['parent_id'] : null;
            $depth++;
        }
        
        echo "   ✅ Hierarchy pre: {$category['name']}\n";
    }
}

/**
 * Vytvorí path pre kategóriu
 */
function buildPath(array $category, array $allCategories): string
{
    $path = '/' . $category['slug'];
    $parentId = $category['parent_id'];
    
    while ($parentId) {
        $parent = array_filter($allCategories, fn($c) => $c['id'] == $parentId);
        $parent = reset($parent);
        
        if ($parent) {
            $path = '/' . $parent['slug'] . $path;
            $parentId = $parent['parent_id'];
        } else {
            break;
        }
    }
    
    return $path;
}

/**
 * Priradí existujúci content do kategórií
 */
function assignContentToCategories(PDO $pdo, array $categoryIds): void
{
    echo "\n📂 Priraďujem existujúci content do kategórií...\n";
    
    // Slovenská dokumentácia
    $pdo->prepare("
        UPDATE orbit_content 
        SET category_id = :category_id 
        WHERE type = 'docs' AND slug LIKE 'sk/%'
    ")->execute(['category_id' => $categoryIds['dokumentacia-sk']]);
    
    // Anglická dokumentácia
    $pdo->prepare("
        UPDATE orbit_content 
        SET category_id = :category_id 
        WHERE type = 'docs' AND slug LIKE 'en/%'
    ")->execute(['category_id' => $categoryIds['dokumentacia-en']]);
    
    // Blog posty
    $pdo->prepare("
        UPDATE orbit_content 
        SET category_id = :category_id 
        WHERE type = 'post'
    ")->execute(['category_id' => $categoryIds['blog-novinky']]);
    
    // Stránky
    $pdo->prepare("
        UPDATE orbit_content 
        SET category_id = :category_id 
        WHERE type = 'page'
    ")->execute(['category_id' => $categoryIds['stranky']]);
    
    echo "   ✅ Slovenská dokumentácia → Dokumentácia/Slovensky\n";
    echo "   ✅ Anglická dokumentácia → Dokumentácia/English\n";
    echo "   ✅ Blog posty → Blog/Novinky\n";
    echo "   ✅ Stránky → Stránky\n";
}
