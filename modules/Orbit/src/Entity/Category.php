<?php

declare(strict_types=1);

namespace Orbit\Entity;

use DateTime;
use DateTimeInterface;

/**
 * Category Entity
 * 
 * Reprezentuje kategóriu v Orbit CMS.
 */
class Category
{
    private ?int $id = null;
    private string $name;
    private string $slug;
    private ?string $description = null;
    private ?int $parentId = null;
    private ?Category $parent = null;
    private array $children = [];
    private string $color = '#6b7280';
    private string $icon = 'folder';
    private int $sortOrder = 0;
    private bool $isActive = true;
    private ?DateTime $createdAt = null;
    private ?DateTime $updatedAt = null;
    
    // Hierarchy data
    private string $path = '';
    private int $depth = 0;
    private int $contentCount = 0;

    public function __construct(string $name, string $slug)
    {
        $this->name = $name;
        $this->slug = $slug;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function getParent(): ?Category
    {
        return $this->parent;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function getContentCount(): int
    {
        return $this->contentCount;
    }

    // Setters
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setParentId(?int $parentId): self
    {
        $this->parentId = $parentId;
        return $this;
    }

    public function setParent(?Category $parent): self
    {
        $this->parent = $parent;
        if ($parent) {
            $this->parentId = $parent->getId();
        }
        return $this;
    }

    public function setChildren(array $children): self
    {
        $this->children = $children;
        return $this;
    }

    public function addChild(Category $child): self
    {
        $this->children[] = $child;
        $child->setParent($this);
        return $this;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;
        return $this;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = DateTime::createFromInterface($createdAt);
        return $this;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = DateTime::createFromInterface($updatedAt);
        return $this;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function setDepth(int $depth): self
    {
        $this->depth = $depth;
        return $this;
    }

    public function setContentCount(int $contentCount): self
    {
        $this->contentCount = $contentCount;
        return $this;
    }

    // Helper methods
    public function getUrl(): string
    {
        return "/category{$this->path}";
    }

    public function getEditUrl(): string
    {
        return "/mark/orbit/categories/{$this->id}/edit";
    }

    public function isRoot(): bool
    {
        return $this->parentId === null;
    }

    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    public function getFullName(): string
    {
        if ($this->parent) {
            return $this->parent->getFullName() . ' → ' . $this->name;
        }
        return $this->name;
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $current = $this;
        
        while ($current) {
            array_unshift($breadcrumbs, $current);
            $current = $current->getParent();
        }
        
        return $breadcrumbs;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parentId,
            'color' => $this->color,
            'icon' => $this->icon,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'path' => $this->path,
            'depth' => $this->depth,
            'content_count' => $this->contentCount,
            'url' => $this->getUrl(),
            'full_name' => $this->getFullName(),
            'has_children' => $this->hasChildren(),
        ];
    }
}
