<?php

declare(strict_types=1);

namespace User\Service;

use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;

/**
 * Mezzio-compatible UserRepository implementation
 */
class MezzioUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private AuthenticationService $authService
    ) {
    }

    public function authenticate(string $credential, ?string $password = null): ?UserInterface
    {
        if (!$password) {
            return null;
        }

        return $this->authService->authenticate($credential, $password);
    }
}
