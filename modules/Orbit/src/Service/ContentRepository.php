<?php

declare(strict_types=1);

namespace Orbit\Service;

use Orbit\Entity\Content;
use Orbit\Entity\Category;
use Orbit\Entity\Tag;
use PDO;
use DateTime;

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
            SELECT c.*, cat.name as category_name, cat.slug as category_slug, cat.color as category_color
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

    public function findByTypeAndSlug(string $type, string $slug): ?Content
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, cat.name as category_name, cat.slug as category_slug, cat.color as category_color
            FROM orbit_content c
            LEFT JOIN orbit_categories cat ON c.category_id = cat.id
            WHERE c.type = :type AND c.slug = :slug
        ");
        
        $stmt->execute(['type' => $type, 'slug' => $slug]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findAll(?string $type = null, bool $publishedOnly = true): array
    {
        $sql = "
            SELECT c.*, cat.name as category_name, cat.slug as category_slug, cat.color as category_color
            FROM orbit_content c
            LEFT JOIN orbit_categories cat ON c.category_id = cat.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($type) {
            $sql .= " AND c.type = :type";
            $params['type'] = $type;
        }
        
        if ($publishedOnly) {
            $sql .= " AND c.published = 1";
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        $content = [];
        while ($row = $stmt->fetch()) {
            if (is_array($row)) {
                $content[] = $this->hydrate($row);
            }
        }
        
        return $content;
    }

    public function findFeatured(?string $type = null, int $limit = 5): array
    {
        $sql = "
            SELECT c.*, cat.name as category_name, cat.slug as category_slug, cat.color as category_color
            FROM orbit_content c
            LEFT JOIN orbit_categories cat ON c.category_id = cat.id
            WHERE c.featured = 1 AND c.published = 1
        ";
        
        $params = [];
        
        if ($type) {
            $sql .= " AND c.type = :type";
            $params['type'] = $type;
        }
        
        $sql .= " ORDER BY c.published_at DESC, c.created_at DESC LIMIT :limit";
        $params['limit'] = $limit;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        $content = [];
        while ($row = $stmt->fetch()) {
            if (is_array($row)) {
                $content[] = $this->hydrate($row);
            }
        }
        
        return $content;
    }

    public function findByCategory(int $categoryId, bool $publishedOnly = true): array
    {
        $sql = "
            SELECT c.*, cat.name as category_name, cat.slug as category_slug, cat.color as category_color
            FROM orbit_content c
            LEFT JOIN orbit_categories cat ON c.category_id = cat.id
            WHERE c.category_id = :category_id
        ";
        
        $params = ['category_id' => $categoryId];
        
        if ($publishedOnly) {
            $sql .= " AND c.published = 1";
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        $content = [];
        while ($row = $stmt->fetch()) {
            if (is_array($row)) {
                $content[] = $this->hydrate($row);
            }
        }
        
        return $content;
    }

    public function findByTag(int $tagId, bool $publishedOnly = true): array
    {
        $sql = "
            SELECT c.*, cat.name as category_name, cat.slug as category_slug, cat.color as category_color
            FROM orbit_content c
            LEFT JOIN orbit_categories cat ON c.category_id = cat.id
            INNER JOIN orbit_content_tags ct ON c.id = ct.content_id
            WHERE ct.tag_id = :tag_id
        ";
        
        $params = ['tag_id' => $tagId];
        
        if ($publishedOnly) {
            $sql .= " AND c.published = 1";
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        $content = [];
        while ($row = $stmt->fetch()) {
            if (is_array($row)) {
                $content[] = $this->hydrate($row);
            }
        }
        
        return $content;
    }

    public function save(Content $content): bool
    {
        if ($content->getId()) {
            return $this->update($content);
        } else {
            return $this->insert($content);
        }
    }

    public function delete(Content $content): bool
    {
        if (!$content->getId()) {
            return false;
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM orbit_content WHERE id = :id");
        return $stmt->execute(['id' => $content->getId()]);
    }

    private function insert(Content $content): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO orbit_content (
                type, slug, title, file_path, meta_data, content_hash,
                published, featured, category_id, created_at, updated_at, published_at
            ) VALUES (
                :type, :slug, :title, :file_path, :meta_data, :content_hash,
                :published, :featured, :category_id, :created_at, :updated_at, :published_at
            )
        ");
        
        $result = $stmt->execute([
            'type' => $content->getType(),
            'slug' => $content->getSlug(),
            'title' => $content->getTitle(),
            'file_path' => $content->getFilePath(),
            'meta_data' => json_encode($content->getMetaData()),
            'content_hash' => $content->getContentHash(),
            'published' => $content->isPublished() ? 1 : 0,
            'featured' => $content->isFeatured() ? 1 : 0,
            'category_id' => $content->getCategoryId(),
            'created_at' => $content->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updated_at' => $content->getUpdatedAt()?->format('Y-m-d H:i:s'),
            'published_at' => $content->getPublishedAt()?->format('Y-m-d H:i:s'),
        ]);
        
        if ($result) {
            $content->setId((int) $this->pdo->lastInsertId());
        }
        
        return $result;
    }

    private function update(Content $content): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE orbit_content SET
                type = :type,
                slug = :slug,
                title = :title,
                file_path = :file_path,
                meta_data = :meta_data,
                content_hash = :content_hash,
                published = :published,
                featured = :featured,
                category_id = :category_id,
                updated_at = :updated_at,
                published_at = :published_at
            WHERE id = :id
        ");
        
        return $stmt->execute([
            'id' => $content->getId(),
            'type' => $content->getType(),
            'slug' => $content->getSlug(),
            'title' => $content->getTitle(),
            'file_path' => $content->getFilePath(),
            'meta_data' => json_encode($content->getMetaData()),
            'content_hash' => $content->getContentHash(),
            'published' => $content->isPublished() ? 1 : 0,
            'featured' => $content->isFeatured() ? 1 : 0,
            'category_id' => $content->getCategoryId(),
            'updated_at' => (new DateTime())->format('Y-m-d H:i:s'),
            'published_at' => $content->getPublishedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    private function hydrate(array $row): Content
    {
        $content = new Content(
            $row['type'],
            $row['slug'],
            $row['title'],
            $row['file_path']
        );
        
        $content->setId((int) $row['id']);
        $metaData = json_decode($row['meta_data'] ?? '{}', true);
        assert(is_array($metaData));
        $content->setMetaData($metaData);
        $content->setContentHash($row['content_hash']);
        $content->setPublished((bool) $row['published']);
        $content->setFeatured((bool) $row['featured']);
        $content->setCategoryId($row['category_id'] ? (int) $row['category_id'] : null);
        
        if ($row['created_at']) {
            $content->setCreatedAt(new DateTime($row['created_at']));
        }
        
        if ($row['updated_at']) {
            $content->setUpdatedAt(new DateTime($row['updated_at']));
        }
        
        if ($row['published_at']) {
            $content->setPublishedAt(new DateTime($row['published_at']));
        }
        
        // Set category if available
        if ($row['category_name']) {
            $category = new Category($row['category_name'], $row['category_slug']);
            $category->setId((int) $row['category_id']);
            $category->setColor($row['category_color']);
            $content->setCategory($category);
        }
        
        return $content;
    }
}
