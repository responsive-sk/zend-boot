<?php

declare(strict_types=1);

namespace User\Service;

use PDO;
use User\Entity\User;

/**
 * PDO-based user repository with SQLite database
 */
class UserRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        return $data ? $this->createUserFromData($data) : null;
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $data = $stmt->fetch();

        return $data ? $this->createUserFromData($data) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $data = $stmt->fetch();

        return $data ? $this->createUserFromData($data) : null;
    }

    public function save(User $user): User
    {
        if ($user->getId() === null) {
            return $this->insert($user);
        } else {
            return $this->update($user);
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * @return array<User>
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users ORDER BY created_at DESC');
        $users = [];

        while ($data = $stmt->fetch()) {
            if ($data !== false) {
                $users[] = $this->createUserFromData($data);
            }
        }

        return $users;
    }

    /**
     * @return array<User>
     */
    public function findByRole(string $role): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE roles LIKE ?');
        $stmt->execute(['%"' . $role . '"%']);
        $users = [];

        while ($data = $stmt->fetch()) {
            if ($data !== false) {
                $user = $this->createUserFromData($data);
                if ($user->hasRole($role)) {
                    $users[] = $user;
                }
            }
        }

        return $users;
    }

    /**
     * @return array<User>
     */
    public function findRecentlyActive(int $limit = 10): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM users
            WHERE last_login_at IS NOT NULL
            ORDER BY last_login_at DESC
            LIMIT ?
        ');
        $stmt->execute([$limit]);

        $users = [];
        while ($data = $stmt->fetch()) {
            if ($data !== false) {
                $users[] = $this->createUserFromData($data);
            }
        }

        return $users;
    }

    public function usernameExists(string $username): bool
    {
        return $this->findByUsername($username) !== null;
    }

    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    private function insert(User $user): User
    {
        $sql = "
            INSERT INTO users (username, email, password_hash, roles, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $user->getUsername(),
            $user->getEmail(),
            $user->getPasswordHash(),
            json_encode($user->getRoles()),
            $user->isActive() ? 1 : 0,
            $user->getCreatedAt()->format('Y-m-d H:i:s')
        ]);

        $user->setId((int) $this->pdo->lastInsertId());
        return $user;
    }

    private function update(User $user): User
    {
        $sql = "
            UPDATE users
            SET username = ?, email = ?, password_hash = ?, roles = ?,
                is_active = ?, last_login_at = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $user->getUsername(),
            $user->getEmail(),
            $user->getPasswordHash(),
            json_encode($user->getRoles()),
            $user->isActive() ? 1 : 0,
            $user->getLastLoginAt()?->format('Y-m-d H:i:s'),
            $user->getId()
        ]);

        return $user;
    }

    private function createUserFromData(array $data): User
    {
        $user = new User(
            $data['username'],
            $data['email'],
            $data['password_hash'],
            json_decode($data['roles'], true) ?: []
        );

        $user->setId((int) $data['id']);
        $user->setActive((bool) $data['is_active']);

        if ($data['last_login_at']) {
            $user->setLastLoginAt(new \DateTimeImmutable($data['last_login_at']));
        }

        return $user;
    }
}
