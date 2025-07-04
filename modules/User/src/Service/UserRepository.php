<?php

declare(strict_types=1);

namespace User\Service;

use User\Entity\User;

/**
 * Simple in-memory user repository for demo purposes
 * In production, this would be replaced with database implementation
 */
class UserRepository
{
    private array $users = [];
    private int $nextId = 1;

    public function __construct()
    {
        // Create default admin user
        $admin = new User(
            'admin',
            'admin@example.com',
            password_hash('admin123', PASSWORD_DEFAULT),
            ['admin', 'user']
        );
        $admin->setId($this->nextId++);
        $this->users[$admin->getId()] = $admin;

        // Create default user
        $user = new User(
            'user',
            'user@example.com',
            password_hash('user123', PASSWORD_DEFAULT),
            ['user']
        );
        $user->setId($this->nextId++);
        $this->users[$user->getId()] = $user;
    }

    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function findByUsername(string $username): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getUsername() === $username) {
                return $user;
            }
        }
        return null;
    }

    public function findByEmail(string $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getEmail() === $email) {
                return $user;
            }
        }
        return null;
    }

    public function save(User $user): User
    {
        if ($user->getId() === null) {
            $user->setId($this->nextId++);
        }
        
        $this->users[$user->getId()] = $user;
        return $user;
    }

    public function delete(int $id): bool
    {
        if (isset($this->users[$id])) {
            unset($this->users[$id]);
            return true;
        }
        return false;
    }

    public function findAll(): array
    {
        return array_values($this->users);
    }

    public function findByRole(string $role): array
    {
        return array_values(array_filter(
            $this->users,
            fn(User $user) => $user->hasRole($role)
        ));
    }

    public function usernameExists(string $username): bool
    {
        return $this->findByUsername($username) !== null;
    }

    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }
}
