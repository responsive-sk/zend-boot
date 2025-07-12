#!/usr/bin/env php
<?php
/**
 * Orbit Database Helper
 * 
 * Utility script pre prácu s Orbit CMS databázou.
 */

declare(strict_types=1);

$rootDir = dirname(__DIR__);
$dbPath = $rootDir . '/data/orbit.db';

if (!file_exists($dbPath)) {
    echo "❌ Orbit databáza neexistuje. Spusti najprv: php bin/migrate-orbit-db.php\n";
    exit(1);
}

$command = $argv[1] ?? 'help';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    switch ($command) {
        case 'stats':
            showStats($pdo);
            break;
            
        case 'content':
            listContent($pdo, $argv[2] ?? null);
            break;
            
        case 'tags':
            listTags($pdo);
            break;

        case 'categories':
            listCategories($pdo);
            break;
            
        case 'search':
            if (!isset($argv[2])) {
                echo "❌ Použitie: php bin/orbit-db-helper.php search \"hľadaný text\"\n";
                exit(1);
            }
            searchContent($pdo, $argv[2]);
            break;
            
        case 'reindex':
            reindexContent($pdo);
            break;
            
        case 'clean':
            cleanCache($pdo);
            break;
            
        default:
            showHelp();
    }
    
} catch (PDOException $e) {
    echo "❌ Chyba databázy: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Zobrazí štatistiky databázy
 */
function showStats(PDO $pdo): void
{
    echo "📊 Orbit CMS Database Stats\n";
    echo "===========================\n\n";
    
    // Content stats
    $stmt = $pdo->query("
        SELECT type, COUNT(*) as count, 
               SUM(CASE WHEN published = 1 THEN 1 ELSE 0 END) as published
        FROM orbit_content 
        GROUP BY type
    ");
    
    echo "📄 Content:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("   %s: %d total (%d published)\n", 
            ucfirst($row['type']), $row['count'], $row['published']);
    }
    
    // Tags stats
    $tagCount = $pdo->query("SELECT COUNT(*) FROM orbit_tags")->fetchColumn();
    echo "\n🏷️  Tags: $tagCount\n";

    // Categories stats
    $categoryCount = $pdo->query("SELECT COUNT(*) FROM orbit_categories")->fetchColumn();
    echo "📂 Categories: $categoryCount\n";
    
    // Media stats
    $mediaCount = $pdo->query("SELECT COUNT(*) FROM orbit_media")->fetchColumn();
    echo "🖼️  Media: $mediaCount\n";
    
    // Cache stats
    $cacheCount = $pdo->query("SELECT COUNT(*) FROM orbit_cache")->fetchColumn();
    echo "💾 Cache entries: $cacheCount\n";
    
    // Database size
    global $dbPath;
    $size = filesize($dbPath);
    echo "\n📊 Database size: " . formatBytes($size) . "\n";
}

/**
 * Zobrazí zoznam content
 */
function listContent(PDO $pdo, ?string $type = null): void
{
    $sql = "SELECT type, slug, title, published, created_at FROM orbit_content";
    $params = [];
    
    if ($type) {
        $sql .= " WHERE type = :type";
        $params['type'] = $type;
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT 20";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    echo "📄 Content" . ($type ? " ($type)" : "") . ":\n";
    echo "=====================================\n";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status = $row['published'] ? '✅' : '❌';
        echo sprintf("%s [%s] %s (%s)\n", 
            $status, $row['type'], $row['title'], $row['slug']);
    }
}

/**
 * Zobrazí zoznam tagov
 */
function listTags(PDO $pdo): void
{
    $stmt = $pdo->query("
        SELECT t.name, t.slug, t.color, COUNT(ct.content_id) as usage_count
        FROM orbit_tags t
        LEFT JOIN orbit_content_tags ct ON t.id = ct.tag_id
        GROUP BY t.id
        ORDER BY usage_count DESC, t.name
    ");

    echo "🏷️  Tags:\n";
    echo "=====================================\n";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("%s (%s) - %d použití\n",
            $row['name'], $row['color'], $row['usage_count']);
    }
}

/**
 * Zobrazí zoznam kategórií
 */
function listCategories(PDO $pdo): void
{
    $stmt = $pdo->query("
        SELECT c.id, c.name, c.slug, c.color, c.parent_id, c.icon,
               COUNT(oc.id) as content_count,
               h.path
        FROM orbit_categories c
        LEFT JOIN orbit_content oc ON c.id = oc.category_id
        LEFT JOIN orbit_category_hierarchy h ON c.id = h.category_id AND h.depth = 0
        GROUP BY c.id
        ORDER BY c.sort_order, c.name
    ");

    echo "📂 Categories:\n";
    echo "=====================================\n";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $indent = $row['parent_id'] ? '  └─ ' : '';
        echo sprintf("%s%s (%s) - %d položiek\n",
            $indent, $row['name'], $row['color'], $row['content_count']);
        echo sprintf("     Path: %s\n", $row['path']);
    }
}

/**
 * Vyhľadá v obsahu
 */
function searchContent(PDO $pdo, string $query): void
{
    echo "🔍 Vyhľadávanie: \"$query\"\n";
    echo "=====================================\n";
    
    // Najprv skúsime FTS5 search
    try {
        $stmt = $pdo->prepare("
            SELECT c.type, c.slug, c.title,
                   snippet(orbit_fts, 1, '<mark>', '</mark>', '...', 32) as snippet
            FROM orbit_content c
            JOIN orbit_fts ON orbit_fts.rowid = c.id
            WHERE orbit_fts MATCH :query
            ORDER BY rank
            LIMIT 10
        ");
        
        $stmt->execute(['query' => $query]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($results)) {
            echo "❌ Žiadne výsledky pre \"$query\"\n";
            return;
        }
        
        foreach ($results as $result) {
            echo sprintf("[%s] %s\n", $result['type'], $result['title']);
            echo "   Slug: /{$result['slug']}\n";
            if ($result['snippet']) {
                echo "   Preview: " . strip_tags($result['snippet']) . "\n";
            }
            echo "\n";
        }
        
    } catch (PDOException $e) {
        echo "❌ Chyba vyhľadávania: " . $e->getMessage() . "\n";
    }
}

/**
 * Reindexuje obsah pre vyhľadávanie
 */
function reindexContent(PDO $pdo): void
{
    echo "🔄 Reindexujem obsah pre vyhľadávanie...\n";
    
    // Vyčisti search index
    $pdo->exec("DELETE FROM orbit_search_index");
    $pdo->exec("DELETE FROM orbit_fts");
    
    // Reindexuj všetok content
    $stmt = $pdo->query("SELECT id, file_path, title FROM orbit_content");
    $insertStmt = $pdo->prepare("
        INSERT INTO orbit_search_index (content_id, title, content, tags, meta_keywords)
        VALUES (:content_id, :title, :content, :tags, :meta_keywords)
    ");
    
    $count = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $filePath = dirname(__DIR__) . '/' . $row['file_path'];
        
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            
            // Odstráň YAML front-matter z obsahu
            $content = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $content);
            
            $insertStmt->execute([
                'content_id' => $row['id'],
                'title' => $row['title'],
                'content' => strip_tags($content),
                'tags' => '', // TODO: načítaj tagy
                'meta_keywords' => '', // TODO: načítaj z meta_data
            ]);
            
            $count++;
            echo "   ✅ {$row['title']}\n";
        }
    }
    
    echo "\n🎉 Reindexovaných $count položiek\n";
}

/**
 * Vyčistí cache
 */
function cleanCache(PDO $pdo): void
{
    $count = $pdo->exec("DELETE FROM orbit_cache WHERE expires_at < datetime('now')");
    echo "🧹 Vyčistených $count expired cache entries\n";
}

/**
 * Zobrazí help
 */
function showHelp(): void
{
    echo "🛠️  Orbit Database Helper\n";
    echo "========================\n\n";
    echo "Použitie: php bin/orbit-db-helper.php [command]\n\n";
    echo "Dostupné príkazy:\n";
    echo "  stats              Zobrazí štatistiky databázy\n";
    echo "  content [type]     Zobrazí zoznam content (voliteľne filtrovaný podľa typu)\n";
    echo "  tags               Zobrazí zoznam tagov\n";
    echo "  categories         Zobrazí zoznam kategórií\n";
    echo "  search \"text\"      Vyhľadá v obsahu\n";
    echo "  reindex            Reindexuje obsah pre vyhľadávanie\n";
    echo "  clean              Vyčistí expired cache\n";
    echo "  help               Zobrazí túto nápovedu\n\n";
    echo "Príklady:\n";
    echo "  php bin/orbit-db-helper.php stats\n";
    echo "  php bin/orbit-db-helper.php content docs\n";
    echo "  php bin/orbit-db-helper.php search \"mezzio\"\n";
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
