<?php

declare(strict_types=1);

namespace Mezzio\Authentication\UserRepository;

use Mezzio\Authentication\Exception;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use Webmozart\Assert\Assert;

use function explode;
use function fclose;
use function fgets;
use function file_exists;
use function fopen;
use function password_verify;
use function sprintf;
use function str_starts_with;
use function trim;

/**
 * Adapter for Apache htpasswd file
 * It supports only bcrypt hash password for security reason
 *
 * @see https://httpd.apache.org/docs/2.4/programs/htpasswd.html
 *
 * @final
 */
class Htpasswd implements UserRepositoryInterface
{
    private readonly string $filename;

    /**
     * @var callable
     * @psalm-var callable(string, array<int|string, string>, array<string, mixed>): UserInterface
     */
    private $userFactory;

    /**
     * @psalm-param callable(string, array<int|string, string>, array<string, mixed>): UserInterface $userFactory
     * @throws Exception\InvalidConfigException
     */
    public function __construct(string $filename, callable $userFactory)
    {
        if (! file_exists($filename)) {
            throw new Exception\InvalidConfigException(sprintf(
                'I cannot access the htpasswd file %s',
                $filename
            ));
        }
        $this->filename = $filename;

        // Provide type safety for the composed user factory.
        $this->userFactory = static function (
            string $identity,
            array $roles = [],
            array $details = []
        ) use ($userFactory): UserInterface {
            Assert::allString($roles);
            Assert::isMap($details);

            return $userFactory($identity, $roles, $details);
        };
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(string $credential, ?string $password = null): ?UserInterface
    {
        if (! $handle = fopen($this->filename, 'r')) {
            return null;
        }
        $found = false;
        $hash  = null;
        while (! $found && ($line = fgets($handle)) !== false) {
            [$name, $hash] = explode(':', $line);
            if ($credential !== $name) {
                continue;
            }
            $hash = trim($hash);
            $this->checkBcryptHash($hash);
            $found = true;
        }
        fclose($handle);

        Assert::stringNotEmpty($hash);

        if ($found && password_verify($password ?? '', $hash)) {
            return ($this->userFactory)($credential, [], []);
        }
        return null;
    }

    /**
     * Check bcrypt usage for security reason
     *
     * @throws Exception\RuntimeException
     */
    protected function checkBcryptHash(string $hash): void
    {
        if (! str_starts_with($hash, '$2y$')) {
            throw new Exception\RuntimeException(
                'The htpasswd file uses not secure hash algorithm. Please use bcrypt.'
            );
        }
    }
}
