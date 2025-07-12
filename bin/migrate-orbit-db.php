#!/usr/bin/env php
<?php
/**
 * Orbit CMS Database Migration
 * 
 * Vytvorí SQLite databázu pre Orbit CMS s potrebnými tabuľkami.
 */

declare(strict_types=1);

$rootDir = dirname(__DIR__);
$dbPath = $rootDir . '/data/orbit.db';
$dataDir = dirname($dbPath);

echo "🗄️  Orbit CMS Database Migration\n";
echo "=================================\n\n";

// Vytvor data adresár ak neexistuje
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
    echo "📁 Vytvorený adresár: $dataDir\n";
}

try {
    // Pripoj sa k SQLite databáze (vytvorí ju ak neexistuje)
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔗 Pripojenie k databáze: $dbPath\n\n";
    
    // Vytvor tabuľky
    createTables($pdo);
    
    // Vytvor indexy
    createIndexes($pdo);
    
    // Naplň základné dáta
    seedData($pdo);
    
    echo "\n🎉 Orbit databáza úspešne vytvorená!\n";
    echo "=================================\n";
    echo "📍 Umiestnenie: $dbPath\n";
    echo "📊 Veľkosť: " . formatBytes(filesize($dbPath)) . "\n";
    echo "🔧 Ďalší krok: Implementácia Orbit modulu\n\n";
    
} catch (PDOException $e) {
    echo "❌ Chyba pri vytváraní databázy: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Vytvorí všetky potrebné tabuľky
 */
function createTables(PDO $pdo): void
{
    echo "📋 Vytváram tabuľky...\n";
    
    // Hlavná tabuľka pre content
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orbit_content (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type VARCHAR(50) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            title VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            meta_data TEXT,
            content_hash VARCHAR(64),
            published BOOLEAN DEFAULT 1,
            featured BOOLEAN DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            published_at DATETIME
        )
    ");
    echo "   ✅ orbit_content\n";
    
    // Tabuľka pre tagy
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orbit_tags (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) UNIQUE NOT NULL,
            slug VARCHAR(100) UNIQUE NOT NULL,
            color VARCHAR(7) DEFAULT '#6366f1',
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "   ✅ orbit_tags\n";
    
    // Pivot tabuľka pre content-tags
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orbit_content_tags (
            content_id INTEGER,
            tag_id INTEGER,
            PRIMARY KEY (content_id, tag_id),
            FOREIGN KEY (content_id) REFERENCES orbit_content(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES orbit_tags(id) ON DELETE CASCADE
        )
    ");
    echo "   ✅ orbit_content_tags\n";
    
    // Search index tabuľka
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orbit_search_index (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            content_id INTEGER UNIQUE,
            title TEXT,
            content TEXT,
            tags TEXT,
            meta_keywords TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (content_id) REFERENCES orbit_content(id) ON DELETE CASCADE
        )
    ");
    echo "   ✅ orbit_search_index\n";
    
    // FTS5 virtual table pre full-text search
    $pdo->exec("
        CREATE VIRTUAL TABLE IF NOT EXISTS orbit_fts USING fts5(
            title, content, tags, meta_keywords,
            content='orbit_search_index',
            content_rowid='id'
        )
    ");
    echo "   ✅ orbit_fts (FTS5)\n";
    
    // Tabuľka pre media súbory
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orbit_media (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            mime_type VARCHAR(100),
            file_size INTEGER,
            alt_text TEXT,
            caption TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "   ✅ orbit_media\n";
    
    // Tabuľka pre cache
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orbit_cache (
            key VARCHAR(255) PRIMARY KEY,
            value TEXT,
            expires_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "   ✅ orbit_cache\n";
}

/**
 * Vytvorí indexy pre optimalizáciu
 */
function createIndexes(PDO $pdo): void
{
    echo "\n🔍 Vytváram indexy...\n";
    
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_content_type ON orbit_content(type)",
        "CREATE INDEX IF NOT EXISTS idx_content_published ON orbit_content(published)",
        "CREATE INDEX IF NOT EXISTS idx_content_featured ON orbit_content(featured)",
        "CREATE INDEX IF NOT EXISTS idx_content_published_at ON orbit_content(published_at)",
        "CREATE INDEX IF NOT EXISTS idx_content_slug ON orbit_content(slug)",
        "CREATE INDEX IF NOT EXISTS idx_tags_slug ON orbit_tags(slug)",
        "CREATE INDEX IF NOT EXISTS idx_media_filename ON orbit_media(filename)",
        "CREATE INDEX IF NOT EXISTS idx_cache_expires ON orbit_cache(expires_at)",
    ];
    
    foreach ($indexes as $index) {
        $pdo->exec($index);
        echo "   ✅ " . substr($index, strpos($index, 'idx_')) . "\n";
    }
}

/**
 * Naplní základné dáta
 */
function seedData(PDO $pdo): void
{
    echo "\n🌱 Napĺňam základné dáta...\n";
    
    // Základné tagy
    $tags = [
        ['name' => 'Dokumentácia', 'slug' => 'dokumentacia', 'color' => '#10b981'],
        ['name' => 'Tutorial', 'slug' => 'tutorial', 'color' => '#3b82f6'],
        ['name' => 'PHP', 'slug' => 'php', 'color' => '#8b5cf6'],
        ['name' => 'Mezzio', 'slug' => 'mezzio', 'color' => '#f59e0b'],
        ['name' => 'Orbit CMS', 'slug' => 'orbit-cms', 'color' => '#ef4444'],
    ];
    
    $stmt = $pdo->prepare("
        INSERT OR IGNORE INTO orbit_tags (name, slug, color) 
        VALUES (:name, :slug, :color)
    ");
    
    foreach ($tags as $tag) {
        $stmt->execute($tag);
        echo "   ✅ Tag: {$tag['name']}\n";
    }
    
    // Indexuj existujúci content z content/ adresára
    indexExistingContent($pdo);
}

/**
 * Indexuje existujúci content z content/ adresára
 */
function indexExistingContent(PDO $pdo): void
{
    global $rootDir;
    $contentDir = $rootDir . '/content';
    
    if (!is_dir($contentDir)) {
        return;
    }
    
    echo "\n📚 Indexujem existujúci content...\n";
    
    $stmt = $pdo->prepare("
        INSERT OR REPLACE INTO orbit_content 
        (type, slug, title, file_path, meta_data, content_hash, published, created_at, updated_at) 
        VALUES (:type, :slug, :title, :file_path, :meta_data, :content_hash, :published, :created_at, :updated_at)
    ");
    
    // Indexuj pages
    indexContentType($contentDir . '/pages', 'page', $stmt);
    
    // Indexuj posts  
    indexContentType($contentDir . '/posts', 'post', $stmt);
    
    // Indexuj docs
    indexContentType($contentDir . '/docs/sk', 'docs', $stmt, 'sk/');
    indexContentType($contentDir . '/docs/en', 'docs', $stmt, 'en/');
}

/**
 * Indexuje content z konkrétneho adresára
 */
function indexContentType(string $dir, string $type, PDOStatement $stmt, string $prefix = ''): void
{
    if (!is_dir($dir)) {
        return;
    }
    
    $files = glob($dir . '/*.md');
    
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $filename = basename($file, '.md');
        
        // Parse YAML front-matter
        $meta = parseYamlFrontMatter($content);
        $title = $meta['title'] ?? ucfirst(str_replace('-', ' ', $filename));
        $slug = $prefix . ($meta['slug'] ?? $filename);
        
        $data = [
            'type' => $type,
            'slug' => $slug,
            'title' => $title,
            'file_path' => str_replace(dirname(__DIR__) . '/', '', $file),
            'meta_data' => json_encode($meta),
            'content_hash' => hash('sha256', $content),
            'published' => $meta['published'] ?? true,
            'created_at' => $meta['created_at'] ?? date('Y-m-d H:i:s'),
            'updated_at' => $meta['updated_at'] ?? date('Y-m-d H:i:s'),
        ];
        
        $stmt->execute($data);
        echo "   ✅ $type: $title\n";
    }
}

/**
 * Parsuje YAML front-matter z Markdown súboru
 */
function parseYamlFrontMatter(string $content): array
{
    if (!preg_match('/^---\s*\n(.*?)\n---\s*\n/s', $content, $matches)) {
        return [];
    }
    
    $yaml = $matches[1];
    $meta = [];
    
    // Jednoduchý YAML parser pre základné key: value páry
    foreach (explode("\n", $yaml) as $line) {
        if (preg_match('/^(\w+):\s*"?([^"]*)"?\s*$/', trim($line), $match)) {
            $meta[$match[1]] = $match[2];
        }
    }
    
    return $meta;
}

/**
 * Formátuje veľkosť súboru
 */
function formatBytes(int $size): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $factor = floor((strlen((string)$size) - 1) / 3);
    return sprintf("%.2f %s", $size / pow(1024, $factor), $units[$factor]);
}
