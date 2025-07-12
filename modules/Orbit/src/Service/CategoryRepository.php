<?php

declare(strict_types=1);

namespace Orbit\Service;

use Orbit\Entity\Category;
use PDO;
use DateTime;

/**
 * Category Repository
 * 
 * Správa kategórií v databáze.
 */
class CategoryRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?Category
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, h.path, h.depth,
                   COUNT(oc.id) as content_count
            FROM orbit_categories c
            LEFT JOIN orbit_category_hierarchy h ON c.id = h.category_id AND h.depth = 0
            LEFT JOIN orbit_content oc ON c.id = oc.category_id
            WHERE c.id = :id
            GROUP BY c.id
        ");
        
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findBySlug(string $slug): ?Category
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, h.path, h.depth,
                   COUNT(oc.id) as content_count
            FROM orbit_categories c
            LEFT JOIN orbit_category_hierarchy h ON c.id = h.category_id AND h.depth = 0
            LEFT JOIN orbit_content oc ON c.id = oc.category_id
            WHERE c.slug = :slug
            GROUP BY c.id
        ");
        
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findAll(bool $activeOnly = true): array
    {
        $sql = "
            SELECT c.*, h.path, h.depth,
                   COUNT(oc.id) as content_count
            FROM orbit_categories c
            LEFT JOIN orbit_category_hierarchy h ON c.id = h.category_id AND h.depth = 0
            LEFT JOIN orbit_content oc ON c.id = oc.category_id
            WHERE 1=1
        ";
        
        if ($activeOnly) {
            $sql .= " AND c.is_active = 1";
        }
        
        $sql .= " GROUP BY c.id ORDER BY c.sort_order, c.name";
        
        $stmt = $this->pdo->query($sql);

        if ($stmt === false) {
            return [];
        }

        $categories = [];
        while ($row = $stmt->fetch()) {
            if (is_array($row)) {
                $categories[] = $this->hydrate($row);
            }
        }
        
        return $categories;
    }

    public function getTree(): array
    {
        $categories = $this->findAll();
        $tree = [];
        $lookup = [];
        
        // Vytvor lookup table
        foreach ($categories as $category) {
            $lookup[$category->getId()] = $category;
        }
        
        // Vytvor tree štruktúru
        foreach ($categories as $category) {
            if ($category->getParentId() === null) {
                // Root kategória
                $tree[] = $category;
            } else {
                // Child kategória
                $parent = $lookup[$category->getParentId()] ?? null;
                if ($parent) {
                    $parent->addChild($category);
                }
            }
        }
        
        return $tree;
    }

    public function findChildren(int $parentId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, h.path, h.depth,
                   COUNT(oc.id) as content_count
            FROM orbit_categories c
            LEFT JOIN orbit_category_hierarchy h ON c.id = h.category_id AND h.depth = 0
            LEFT JOIN orbit_content oc ON c.id = oc.category_id
            WHERE c.parent_id = :parent_id AND c.is_active = 1
            GROUP BY c.id
            ORDER BY c.sort_order, c.name
        ");
        
        $stmt->execute(['parent_id' => $parentId]);

        $categories = [];
        while ($row = $stmt->fetch()) {
            if (is_array($row)) {
                $categories[] = $this->hydrate($row);
            }
        }
        
        return $categories;
    }

    public function save(Category $category): bool
    {
        if ($category->getId()) {
            return $this->update($category);
        } else {
            return $this->insert($category);
        }
    }

    public function delete(Category $category): bool
    {
        if (!$category->getId()) {
            return false;
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM orbit_categories WHERE id = :id");
        return $stmt->execute(['id' => $category->getId()]);
    }

    private function insert(Category $category): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO orbit_categories (
                name, slug, description, parent_id, color, icon, 
                sort_order, is_active, created_at, updated_at
            ) VALUES (
                :name, :slug, :description, :parent_id, :color, :icon,
                :sort_order, :is_active, :created_at, :updated_at
            )
        ");
        
        $result = $stmt->execute([
            'name' => $category->getName(),
            'slug' => $category->getSlug(),
            'description' => $category->getDescription(),
            'parent_id' => $category->getParentId(),
            'color' => $category->getColor(),
            'icon' => $category->getIcon(),
            'sort_order' => $category->getSortOrder(),
            'is_active' => $category->isActive() ? 1 : 0,
            'created_at' => $category->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updated_at' => $category->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ]);
        
        if ($result) {
            $category->setId((int) $this->pdo->lastInsertId());
        }
        
        return $result;
    }

    private function update(Category $category): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE orbit_categories SET
                name = :name,
                slug = :slug,
                description = :description,
                parent_id = :parent_id,
                color = :color,
                icon = :icon,
                sort_order = :sort_order,
                is_active = :is_active,
                updated_at = :updated_at
            WHERE id = :id
        ");
        
        return $stmt->execute([
            'id' => $category->getId(),
            'name' => $category->getName(),
            'slug' => $category->getSlug(),
            'description' => $category->getDescription(),
            'parent_id' => $category->getParentId(),
            'color' => $category->getColor(),
            'icon' => $category->getIcon(),
            'sort_order' => $category->getSortOrder(),
            'is_active' => $category->isActive() ? 1 : 0,
            'updated_at' => (new DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

    private function hydrate(array $row): Category
    {
        $category = new Category($row['name'], $row['slug']);
        
        $category->setId((int) $row['id']);
        $category->setDescription($row['description']);
        $category->setParentId($row['parent_id'] ? (int) $row['parent_id'] : null);
        $category->setColor($row['color']);
        $category->setIcon($row['icon']);
        $category->setSortOrder((int) $row['sort_order']);
        $category->setIsActive((bool) $row['is_active']);
        
        if ($row['created_at']) {
            $category->setCreatedAt(new DateTime($row['created_at']));
        }
        
        if ($row['updated_at']) {
            $category->setUpdatedAt(new DateTime($row['updated_at']));
        }
        
        if (isset($row['path'])) {
            $category->setPath($row['path']);
        }
        
        if (isset($row['depth'])) {
            $category->setDepth((int) $row['depth']);
        }
        
        if (isset($row['content_count'])) {
            $category->setContentCount((int) $row['content_count']);
        }
        
        return $category;
    }
}
