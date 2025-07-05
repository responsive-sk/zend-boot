<?php

declare(strict_types=1);

namespace Mark\Entity;

/**
 * HDM Boot Protocol - Mark User Entity
 *
 * Mark users stored in mark.db (separate from regular users)
 * Roles: mark, editor, supermark
 */
class MarkUser
{
    private ?int $id = null;
    private string $username;
    private string $email;
    private string $passwordHash;
    /** @var array<string> */
    private array $roles = [];
    private bool $isActive = true;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $lastLoginAt = null;
    private \DateTimeImmutable $updatedAt;

    /**
     * @param array<string> $roles
     */
    public function __construct(
        string $username,
        string $email,
        string $passwordHash,
        array $roles = ['mark']
    ) {
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->roles = $roles;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function addRole(string $role): void
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function removeRole(string $role): void
    {
        $this->roles = array_values(array_filter($this->roles, fn($r) => $r !== $role));
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(\DateTimeImmutable $lastLoginAt): void
    {
        $this->lastLoginAt = $lastLoginAt;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Check if user is supermark (highest privilege)
     */
    public function isSupermark(): bool
    {
        return $this->hasRole('supermark');
    }

    /**
     * Check if user is editor or higher
     */
    public function isEditor(): bool
    {
        return $this->hasRole('editor') || $this->isSupermark();
    }

    /**
     * Check if user has any mark role
     */
    public function isMarkUser(): bool
    {
        $markRoles = ['mark', 'editor', 'supermark'];
        foreach ($markRoles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'roles' => $this->roles,
            'isActive' => $this->isActive,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'lastLoginAt' => $this->lastLoginAt?->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
