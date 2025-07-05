<?php

declare(strict_types=1);

namespace Mark\Service;

use Mark\Entity\MarkUser;
use PDO;

/**
 * HDM Boot Protocol - Mark User Repository
 * 
 * Manages mark users in mark.db (separate from regular users)
 */
class MarkUserRepository
{
    public function __construct(
        private PDO $markPdo
    ) {
    }

    public function findById(int $id): ?MarkUser
    {
        $stmt = $this->markPdo->prepare('SELECT * FROM mark_users WHERE id = ?');
        $stmt->execute([$id]);
        
        $data = $stmt->fetch();
        if ($data === false) {
            return null;
        }
        
        return $this->createMarkUserFromData($data);
    }

    public function findByUsername(string $username): ?MarkUser
    {
        $stmt = $this->markPdo->prepare('SELECT * FROM mark_users WHERE username = ?');
        $stmt->execute([$username]);
        
        $data = $stmt->fetch();
        if ($data === false) {
            return null;
        }
        
        return $this->createMarkUserFromData($data);
    }

    public function findByEmail(string $email): ?MarkUser
    {
        $stmt = $this->markPdo->prepare('SELECT * FROM mark_users WHERE email = ?');
        $stmt->execute([$email]);
        
        $data = $stmt->fetch();
        if ($data === false) {
            return null;
        }
        
        return $this->createMarkUserFromData($data);
    }

    /**
     * @return array<MarkUser>
     */
    public function findAll(): array
    {
        $stmt = $this->markPdo->query('SELECT * FROM mark_users ORDER BY created_at DESC');
        $users = [];
    if ($stmt === false) {
        throw new \RuntimeException('Failed to execute query for finding all mark users');
    }

        while ($data = $stmt->fetch()) {
            if ($data !== false) {
                $users[] = $this->createMarkUserFromData($data);
            }
        }

        return $users;
    }

    /**
     * @return array<MarkUser>
     */
    public function findByRole(string $role): array
    {
        $stmt = $this->markPdo->prepare('SELECT * FROM mark_users WHERE roles LIKE ?');
        $stmt->execute(['%"' . $role . '"%']);
        $users = [];

        while ($data = $stmt->fetch()) {
            if ($data !== false) {
                $user = $this->createMarkUserFromData($data);
                if ($user->hasRole($role)) {
                    $users[] = $user;
                }
            }
        }

        return $users;
    }

    /**
     * @return array<MarkUser>
     */
    public function findRecentlyActive(int $limit = 10): array
    {
        $stmt = $this->markPdo->prepare('
            SELECT * FROM mark_users 
            WHERE last_login_at IS NOT NULL 
            ORDER BY last_login_at DESC 
            LIMIT ?
        ');
        $stmt->execute([$limit]);
        
        $users = [];
        while ($data = $stmt->fetch()) {
            if ($data !== false) {
                $users[] = $this->createMarkUserFromData($data);
            }
        }
        
        return $users;
    }

    public function save(MarkUser $user): void
    {
        if ($user->getId() === null) {
            $this->insert($user);
        } else {
            $this->update($user);
        }
    }

    private function insert(MarkUser $user): void
    {
        $stmt = $this->markPdo->prepare('
            INSERT INTO mark_users (username, email, password_hash, roles, is_active, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $user->getUsername(),
            $user->getEmail(),
            $user->getPasswordHash(),
            json_encode($user->getRoles()),
            $user->isActive() ? 1 : 0,
            $user->getCreatedAt()->format('Y-m-d H:i:s'),
            $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $user->setId((int) $this->markPdo->lastInsertId());
    }

    private function update(MarkUser $user): void
    {
        $stmt = $this->markPdo->prepare('
            UPDATE mark_users 
            SET username = ?, email = ?, password_hash = ?, roles = ?, is_active = ?, 
                last_login_at = ?, updated_at = ?
            WHERE id = ?
        ');

        $stmt->execute([
            $user->getUsername(),
            $user->getEmail(),
            $user->getPasswordHash(),
            json_encode($user->getRoles()),
            $user->isActive() ? 1 : 0,
            $user->getLastLoginAt()?->format('Y-m-d H:i:s'),
            $user->getUpdatedAt()->format('Y-m-d H:i:s'),
            $user->getId(),
        ]);
    }

    public function delete(MarkUser $user): void
    {
        if ($user->getId() !== null) {
            $stmt = $this->markPdo->prepare('DELETE FROM mark_users WHERE id = ?');
            $stmt->execute([$user->getId()]);
        }
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->markPdo->prepare('SELECT COUNT(*) FROM mark_users WHERE username = ?');
        $stmt->execute([$username]);
        
        return $stmt->fetchColumn() > 0;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->markPdo->prepare('SELECT COUNT(*) FROM mark_users WHERE email = ?');
        $stmt->execute([$email]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
    /**
     * @param array<string, mixed> $data
     */
    private function createMarkUserFromData(array $data): MarkUser
    {
        // Validate required fields
        if (!isset($data['username']) || !is_string($data['username'])) {
            throw new \InvalidArgumentException('Username is required and must be a string');
        }
        if (!isset($data['email']) || !is_string($data['email'])) {
            throw new \InvalidArgumentException('Email is required and must be a string');
        }
        if (!isset($data['password_hash']) || !is_string($data['password_hash'])) {
            throw new \InvalidArgumentException('Password hash is required and must be a string');
        }
        if (!isset($data['id']) || !is_numeric($data['id'])) {
            throw new \InvalidArgumentException('ID is required and must be numeric');
        }

        // Parse roles safely
        $rolesJson = $data['roles'] ?? '[]';
        if (!is_string($rolesJson)) {
            $rolesJson = '[]';
        }
        $roles = json_decode($rolesJson, true);
        if (!is_array($roles)) {
            $roles = [];
        }

        $user = new MarkUser(
            $data['username'],
            $data['email'],
            $data['password_hash'],
            $roles
        );

        $user->setId((int) $data['id']);
        $user->setIsActive((bool) ($data['is_active'] ?? false));

        // Handle last login date safely
        if (isset($data['last_login_at']) && is_string($data['last_login_at']) && !empty($data['last_login_at'])) {
            try {
                $user->setLastLoginAt(new \DateTimeImmutable($data['last_login_at']));
            } catch (\Exception $e) {
                // Invalid date format, skip setting last login
            }
        }

        return $user;
    }
}
