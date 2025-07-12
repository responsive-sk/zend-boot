<?php

declare(strict_types=1);

namespace Orbit\Entity;

use DateTime;
use DateTimeInterface;

/**
 * Content Entity
 * 
 * Reprezentuje obsah v Orbit CMS (stránky, články, dokumentácia).
 */
class Content
{
    private ?int $id = null;
    private string $type;
    private string $slug;
    private string $title;
    private string $filePath;
    private array $metaData = [];
    private ?string $contentHash = null;
    private bool $published = true;
    private bool $featured = false;
    private ?int $categoryId = null;
    private ?Category $category = null;
    private array $tags = [];
    private ?DateTime $createdAt = null;
    private ?DateTime $updatedAt = null;
    private ?DateTime $publishedAt = null;
    
    // Cached content
    private ?string $rawContent = null;
    private ?string $renderedContent = null;

    public function __construct(
        string $type,
        string $slug,
        string $title,
        string $filePath
    ) {
        $this->type = $type;
        $this->slug = $slug;
        $this->title = $title;
        $this->filePath = $filePath;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getMetaData(): array
    {
        return $this->metaData;
    }

    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->metaData[$key] ?? $default;
    }

    public function getContentHash(): ?string
    {
        return $this->contentHash;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getPublishedAt(): ?DateTime
    {
        return $this->publishedAt;
    }

    public function getRawContent(): ?string
    {
        return $this->rawContent;
    }

    public function getRenderedContent(): ?string
    {
        return $this->renderedContent;
    }

    // Setters
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function setMetaData(array $metaData): self
    {
        $this->metaData = $metaData;
        return $this;
    }

    public function setMeta(string $key, mixed $value): self
    {
        $this->metaData[$key] = $value;
        return $this;
    }

    public function setContentHash(string $contentHash): self
    {
        $this->contentHash = $contentHash;
        return $this;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;
        return $this;
    }

    public function setFeatured(bool $featured): self
    {
        $this->featured = $featured;
        return $this;
    }

    public function setCategoryId(?int $categoryId): self
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        if ($category) {
            $this->categoryId = $category->getId();
        }
        return $this;
    }

    public function setTags(array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }

    public function addTag(Tag $tag): self
    {
        $this->tags[] = $tag;
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

    public function setPublishedAt(?DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt ? DateTime::createFromInterface($publishedAt) : null;
        return $this;
    }

    public function setRawContent(string $rawContent): self
    {
        $this->rawContent = $rawContent;
        $this->contentHash = hash('sha256', $rawContent);
        return $this;
    }

    public function setRenderedContent(string $renderedContent): self
    {
        $this->renderedContent = $renderedContent;
        return $this;
    }

    // Helper methods
    public function getUrl(): string
    {
        return match ($this->type) {
            'page' => "/page/{$this->slug}",
            'post' => "/blog/{$this->slug}",
            'docs' => "/docs/{$this->slug}",
            default => "/{$this->type}/{$this->slug}",
        };
    }

    public function getEditUrl(): string
    {
        return "/mark/orbit/content/{$this->type}/{$this->id}/edit";
    }

    public function hasTag(string $tagSlug): bool
    {
        foreach ($this->tags as $tag) {
            if ($tag->getSlug() === $tagSlug) {
                return true;
            }
        }
        return false;
    }

    public function getExcerpt(int $length = 200): string
    {
        $excerpt = $this->getMeta('excerpt');
        if (is_string($excerpt)) {
            return $excerpt;
        }

        if (is_string($this->rawContent)) {
            $text = strip_tags($this->rawContent);
            $text = preg_replace('/^---.*?---/s', '', $text) ?? ''; // Remove YAML front-matter
            $text = trim($text);
            
            if (strlen($text) <= $length) {
                return $text;
            }
            
            return substr($text, 0, $length) . '...';
        }

        return '';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'slug' => $this->slug,
            'title' => $this->title,
            'file_path' => $this->filePath,
            'meta_data' => $this->metaData,
            'content_hash' => $this->contentHash,
            'published' => $this->published,
            'featured' => $this->featured,
            'category_id' => $this->categoryId,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'published_at' => $this->publishedAt?->format('Y-m-d H:i:s'),
            'url' => $this->getUrl(),
            'excerpt' => $this->getExcerpt(),
        ];
    }
}
