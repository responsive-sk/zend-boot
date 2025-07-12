<?php

declare(strict_types=1);

namespace Orbit\Service;

use Orbit\Entity\Tag;
use PDO;
use DateTime;

/**
 * Tag Repository
 * 
 * Správa tagov v databáze.
 */
class TagRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?Tag
    {
        $stmt = $this->pdo->prepare("
            SELECT t.*, COUNT(ct.content_id) as usage_count
            FROM orbit_tags t
            LEFT JOIN orbit_content_tags ct ON t.id = ct.tag_id
            WHERE t.id = :id
            GROUP BY t.id
        ");
        
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findBySlug(string $slug): ?Tag
    {
        $stmt = $this->pdo->prepare("
            SELECT t.*, COUNT(ct.content_id) as usage_count
            FROM orbit_tags t
            LEFT JOIN orbit_content_tags ct ON t.id = ct.tag_id
            WHERE t.slug = :slug
            GROUP BY t.id
        ");
        
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT t.*, COUNT(ct.content_id) as usage_count
            FROM orbit_tags t
            LEFT JOIN orbit_content_tags ct ON t.id = ct.tag_id
            GROUP BY t.id
            ORDER BY t.name
        ");

        if ($stmt === false) {
            return [];
        }

        $tags = [];
        while ($row = $stmt->fetch()) {
            if (is_array($row)) {
                $tags[] = $this->hydrate($row);
            }
        }
        
        return $tags;
    }

    public function findPopular(int $limit = 20): array
    {
        $stmt = $this->pdo->prepare("
            SELECT t.*, COUNT(ct.content_id) as usage_count
            FROM orbit_tags t
            LEFT JOIN orbit_content_tags ct ON t.id = ct.tag_id
            GROUP BY t.id
            HAVING usage_count > 0
            ORDER BY usage_count DESC, t.name
            LIMIT :limit
        ");
        
        $stmt->execute(['limit' => $limit]);

        $tags = [];
        while ($row = $stmt->fetch()) {
            if (is_array($row)) {
                $tags[] = $this->hydrate($row);
            }
        }
        
        return $tags;
    }

    public function findByContent(int $contentId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT t.*, COUNT(ct2.content_id) as usage_count
            FROM orbit_tags t
            INNER JOIN orbit_content_tags ct ON t.id = ct.tag_id
            LEFT JOIN orbit_content_tags ct2 ON t.id = ct2.tag_id
            WHERE ct.content_id = :content_id
            GROUP BY t.id
            ORDER BY t.name
        ");
        
        $stmt->execute(['content_id' => $contentId]);

        $tags = [];
        while ($row = $stmt->fetch()) {
            if (is_array($row)) {
                $tags[] = $this->hydrate($row);
            }
        }
        
        return $tags;
    }

    public function save(Tag $tag): bool
    {
        if ($tag->getId()) {
            return $this->update($tag);
        } else {
            return $this->insert($tag);
        }
    }

    public function delete(Tag $tag): bool
    {
        if (!$tag->getId()) {
            return false;
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM orbit_tags WHERE id = :id");
        return $stmt->execute(['id' => $tag->getId()]);
    }

    public function attachToContent(int $tagId, int $contentId): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT OR IGNORE INTO orbit_content_tags (tag_id, content_id)
            VALUES (:tag_id, :content_id)
        ");
        
        return $stmt->execute([
            'tag_id' => $tagId,
            'content_id' => $contentId,
        ]);
    }

    public function detachFromContent(int $tagId, int $contentId): bool
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM orbit_content_tags 
            WHERE tag_id = :tag_id AND content_id = :content_id
        ");
        
        return $stmt->execute([
            'tag_id' => $tagId,
            'content_id' => $contentId,
        ]);
    }

    public function syncContentTags(int $contentId, array $tagIds): bool
    {
        $this->pdo->beginTransaction();
        
        try {
            // Odstráň všetky existujúce tagy
            $stmt = $this->pdo->prepare("DELETE FROM orbit_content_tags WHERE content_id = :content_id");
            $stmt->execute(['content_id' => $contentId]);
            
            // Pridaj nové tagy
            if (!empty($tagIds)) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO orbit_content_tags (tag_id, content_id)
                    VALUES (:tag_id, :content_id)
                ");
                
                foreach ($tagIds as $tagId) {
                    $stmt->execute([
                        'tag_id' => $tagId,
                        'content_id' => $contentId,
                    ]);
                }
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    private function insert(Tag $tag): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO orbit_tags (name, slug, color, description, created_at)
            VALUES (:name, :slug, :color, :description, :created_at)
        ");
        
        $result = $stmt->execute([
            'name' => $tag->getName(),
            'slug' => $tag->getSlug(),
            'color' => $tag->getColor(),
            'description' => $tag->getDescription(),
            'created_at' => $tag->getCreatedAt()?->format('Y-m-d H:i:s'),
        ]);
        
        if ($result) {
            $tag->setId((int) $this->pdo->lastInsertId());
        }
        
        return $result;
    }

    private function update(Tag $tag): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE orbit_tags SET
                name = :name,
                slug = :slug,
                color = :color,
                description = :description
            WHERE id = :id
        ");
        
        return $stmt->execute([
            'id' => $tag->getId(),
            'name' => $tag->getName(),
            'slug' => $tag->getSlug(),
            'color' => $tag->getColor(),
            'description' => $tag->getDescription(),
        ]);
    }

    private function hydrate(array $row): Tag
    {
        $tag = new Tag($row['name'], $row['slug']);
        
        $tag->setId((int) $row['id']);
        $tag->setColor($row['color']);
        $tag->setDescription($row['description']);
        
        if ($row['created_at']) {
            $tag->setCreatedAt(new DateTime($row['created_at']));
        }
        
        if (isset($row['usage_count'])) {
            $tag->setUsageCount((int) $row['usage_count']);
        }
        
        return $tag;
    }
}
