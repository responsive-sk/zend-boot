<?php

declare(strict_types=1);

namespace Orbit\Entity;

use DateTime;
use DateTimeInterface;

/**
 * Tag Entity
 * 
 * Reprezentuje tag v Orbit CMS.
 */
class Tag
{
    private ?int $id = null;
    private string $name;
    private string $slug;
    private string $color = '#6366f1';
    private ?string $description = null;
    private ?DateTime $createdAt = null;
    private int $usageCount = 0;

    public function __construct(string $name, string $slug)
    {
        $this->name = $name;
        $this->slug = $slug;
        $this->createdAt = new DateTime();
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

    public function getColor(): string
    {
        return $this->color;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUsageCount(): int
    {
        return $this->usageCount;
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

    public function setColor(string $color): self
    {
        $this->color = $color;
        return $this;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = DateTime::createFromInterface($createdAt);
        return $this;
    }

    public function setUsageCount(int $usageCount): self
    {
        $this->usageCount = $usageCount;
        return $this;
    }

    // Helper methods
    public function getUrl(): string
    {
        return "/tag/{$this->slug}";
    }

    public function getEditUrl(): string
    {
        return "/mark/orbit/tags/{$this->id}/edit";
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'color' => $this->color,
            'description' => $this->description,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'usage_count' => $this->usageCount,
            'url' => $this->getUrl(),
        ];
    }
}
