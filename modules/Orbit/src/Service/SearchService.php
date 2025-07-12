<?php

declare(strict_types=1);

namespace Orbit\Service;

use Orbit\Entity\Content;
use PDO;

/**
 * Search Service
 * 
 * Služba pre vyhľadávanie v obsahu pomocou SQLite FTS5.
 */
class SearchService
{
    private PDO $pdo;
    private array $config;

    public function __construct(PDO $pdo, array $config = [])
    {
        $this->pdo = $pdo;
        $this->config = array_merge([
            'min_query_length' => 3,
            'max_results' => 50,
            'highlight_tags' => ['<mark>', '</mark>'],
        ], $config);
    }

    /**
     * Vyhľadá v obsahu
     */
    public function search(string $query, array $filters = []): array
    {
        if (strlen($query) < $this->config['min_query_length']) {
            return [];
        }
        
        $limit = $filters['limit'] ?? $this->config['max_results'];
        $type = $filters['type'] ?? null;
        
        try {
            // Priprav FTS5 query
            $ftsQuery = $this->prepareFtsQuery($query);
            
            $sql = "
                SELECT c.id, c.type, c.slug, c.title, c.updated_at,
                       cat.name as category_name, cat.slug as category_slug,
                       snippet(orbit_fts, 1, :highlight_start, :highlight_end, '...', 32) as snippet
                FROM orbit_content c
                LEFT JOIN orbit_categories cat ON c.category_id = cat.id
                JOIN orbit_fts ON orbit_fts.rowid = c.id
                WHERE orbit_fts MATCH :query
                  AND c.published = 1
            ";
            
            $params = [
                'query' => $ftsQuery,
                'highlight_start' => $this->config['highlight_tags'][0],
                'highlight_end' => $this->config['highlight_tags'][1],
            ];
            
            if ($type) {
                $sql .= " AND c.type = :type";
                $params['type'] = $type;
            }
            
            $sql .= " ORDER BY rank LIMIT :limit";
            $params['limit'] = $limit;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $results = [];
            while ($row = $stmt->fetch()) {
                if (is_array($row)) {
                    $results[] = [
                        'id' => (int) $row['id'],
                        'type' => $row['type'],
                        'slug' => $row['slug'],
                        'title' => $row['title'],
                        'snippet' => $row['snippet'],
                        'category' => $row['category_name'] ? [
                            'name' => $row['category_name'],
                            'slug' => $row['category_slug'],
                        ] : null,
                        'updated_at' => $row['updated_at'],
                    ];
                }
            }
            
            return $results;
            
        } catch (\PDOException $e) {
            // Fallback na LIKE search ak FTS5 zlyhá
            return $this->fallbackSearch($query, $filters);
        }
    }

    /**
     * Indexuje content pre vyhľadávanie
     */
    public function indexContent(Content $content): void
    {
        try {
            // Načítaj obsah súboru ak nie je načítaný
            $rawContent = $content->getRawContent();
            if (!$rawContent && $content->getFilePath()) {
                // Tu by sme mali načítať obsah cez FileDriver
                // Pre teraz použijeme prázdny string
                $rawContent = '';
            }
            
            // Odstráň YAML front-matter z obsahu
            $rawContent = $rawContent ?? '';
            $cleanContent = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $rawContent) ?? $rawContent;
            $cleanContent = strip_tags($cleanContent);
            
            // Získaj tagy
            $tags = implode(' ', array_map(fn($tag) => $tag->getName(), $content->getTags()));
            
            // Upsert do search index
            $stmt = $this->pdo->prepare("
                INSERT OR REPLACE INTO orbit_search_index 
                (content_id, title, content, tags, meta_keywords, updated_at)
                VALUES (:content_id, :title, :content, :tags, :meta_keywords, :updated_at)
            ");
            
            $stmt->execute([
                'content_id' => $content->getId(),
                'title' => $content->getTitle(),
                'content' => $cleanContent,
                'tags' => $tags,
                'meta_keywords' => $content->getMeta('keywords', ''),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            
        } catch (\PDOException $e) {
            // Log error but don't fail
            error_log("Search indexing failed for content {$content->getId()}: " . $e->getMessage());
        }
    }

    /**
     * Odstráni content zo search indexu
     */
    public function removeFromIndex(Content $content): void
    {
        if (!$content->getId()) {
            return;
        }
        
        try {
            $stmt = $this->pdo->prepare("DELETE FROM orbit_search_index WHERE content_id = :content_id");
            $stmt->execute(['content_id' => $content->getId()]);
        } catch (\PDOException $e) {
            error_log("Search index removal failed for content {$content->getId()}: " . $e->getMessage());
        }
    }

    /**
     * Reindexuje všetok content
     */
    public function reindexAll(): int
    {
        // Vyčisti search index
        $this->pdo->exec("DELETE FROM orbit_search_index");
        $this->pdo->exec("DELETE FROM orbit_fts");
        
        // Získaj všetok content
        $stmt = $this->pdo->query("
            SELECT id, type, slug, title, file_path
            FROM orbit_content
            WHERE published = 1
        ");

        if ($stmt === false) {
            return 0;
        }

        $count = 0;
        while ($row = $stmt->fetch()) {
            if (is_array($row)) {
                // Vytvor Content entity a indexuj
                $content = new Content(
                    (string) $row['type'],
                    (string) $row['slug'],
                    (string) $row['title'],
                    (string) $row['file_path']
                );
                $content->setId((int) $row['id']);

                $this->indexContent($content);
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Pripraví FTS5 query
     */
    private function prepareFtsQuery(string $query): string
    {
        // Escape special characters
        $cleanQuery = preg_replace('/[^\w\s-]/', '', $query) ?? $query;

        // Split na slová
        $words = preg_split('/\s+/', trim($cleanQuery));
        if ($words === false) {
            return $query;
        }

        $words = array_filter($words, fn($word) => strlen($word) >= 2);

        if (empty($words)) {
            return $query;
        }

        // Vytvor FTS5 query s prefix matching
        return implode(' AND ', array_map(fn($word) => $word . '*', $words));
    }

    /**
     * Fallback search pomocou LIKE
     */
    private function fallbackSearch(string $query, array $filters = []): array
    {
        $limit = $filters['limit'] ?? $this->config['max_results'];
        $type = $filters['type'] ?? null;
        
        $sql = "
            SELECT c.id, c.type, c.slug, c.title, c.updated_at,
                   cat.name as category_name, cat.slug as category_slug
            FROM orbit_content c
            LEFT JOIN orbit_categories cat ON c.category_id = cat.id
            WHERE c.published = 1
              AND (c.title LIKE :query OR c.slug LIKE :query)
        ";
        
        $params = ['query' => '%' . $query . '%'];
        
        if ($type) {
            $sql .= " AND c.type = :type";
            $params['type'] = $type;
        }
        
        $sql .= " ORDER BY c.updated_at DESC LIMIT :limit";
        $params['limit'] = $limit;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        $results = [];
        while ($row = $stmt->fetch()) {
            if (is_array($row)) {
                $results[] = [
                    'id' => (int) $row['id'],
                    'type' => $row['type'],
                    'slug' => $row['slug'],
                    'title' => $row['title'],
                    'snippet' => '',
                    'category' => $row['category_name'] ? [
                        'name' => $row['category_name'],
                        'slug' => $row['category_slug'],
                    ] : null,
                    'updated_at' => $row['updated_at'],
                ];
            }
        }
        
        return $results;
    }
}
