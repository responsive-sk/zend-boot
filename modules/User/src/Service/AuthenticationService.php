<?php

declare(strict_types=1);

namespace User\Service;

use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use User\Entity\User;

class AuthenticationService implements UserRepositoryInterface
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function authenticate(string $credential, ?string $password = null): ?UserInterface
    {
        // Find user by username or email
        $user = $this->userRepository->findByUsername($credential) 
            ?? $this->userRepository->findByEmail($credential);

        if (!$user || !$user->isActive()) {
            return null;
        }

        if ($password && !$user->verifyPassword($password)) {
            return null;
        }

        // Update last login time
        $user->setLastLoginAt(new \DateTimeImmutable());
        $this->userRepository->save($user);

        return new AuthenticatedUser($user);
    }

    public function findByCredential(string $credential): ?User
    {
        return $this->userRepository->findByUsername($credential) 
            ?? $this->userRepository->findByEmail($credential);
    }

    public function registerUser(string $username, string $email, string $password, array $roles = ['user']): User
    {
        if ($this->userRepository->usernameExists($username)) {
            throw new \InvalidArgumentException('Username already exists');
        }

        if ($this->userRepository->emailExists($email)) {
            throw new \InvalidArgumentException('Email already exists');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $user = new User($username, $email, $passwordHash, $roles);
        
        return $this->userRepository->save($user);
    }

    public function changePassword(User $user, string $newPassword): void
    {
        $user->setPasswordHash(password_hash($newPassword, PASSWORD_DEFAULT));
        $this->userRepository->save($user);
    }

    public function deactivateUser(User $user): void
    {
        $user->setActive(false);
        $this->userRepository->save($user);
    }

    public function activateUser(User $user): void
    {
        $user->setActive(true);
        $this->userRepository->save($user);
    }
}

/**
 * Wrapper class to implement Mezzio UserInterface
 */
class AuthenticatedUser implements UserInterface
{
    public function __construct(
        private User $user
    ) {
    }

    public function getIdentity(): string
    {
        return $this->user->getUsername();
    }

    public function getRoles(): iterable
    {
        return $this->user->getRoles();
    }

    public function getDetail(string $name, $default = null)
    {
        return match ($name) {
            'id' => $this->user->getId(),
            'username' => $this->user->getUsername(),
            'email' => $this->user->getEmail(),
            'roles' => $this->user->getRoles(),
            'isActive' => $this->user->isActive(),
            'createdAt' => $this->user->getCreatedAt(),
            'lastLoginAt' => $this->user->getLastLoginAt(),
            default => $default,
        };
    }

    public function getDetails(): array
    {
        return $this->user->toArray();
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
