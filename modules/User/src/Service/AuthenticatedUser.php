<?php

declare(strict_types=1);

namespace User\Service;

use Mezzio\Authentication\UserInterface;
use User\Entity\User;

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

    /**
     * @param mixed $default
     * @return mixed
     */
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

    /**
     * @return array<string, mixed>
     */
    public function getDetails(): array
    {
        $details = $this->user->toArray();
        // Ensure id is always present
        $details['id'] = $this->user->getId();
        return $details;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
