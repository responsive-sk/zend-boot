<?php

declare(strict_types=1);

namespace Light\App\Factory;

use Psr\Container\ContainerInterface;
use ResponsiveSk\Slim4Paths\Paths;
use ResponsiveSk\Slim4Paths\Security\SecurityConfig;

use function array_merge;
use function assert;
use function dirname;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;

class PathsFactory
{
    public function __invoke(ContainerInterface $container): Paths
    {
        /** @var array<string, mixed> $config */
        $config = $container->get('config');
        assert(is_array($config));

        /** @var array<string, mixed> $pathsConfig */
        $pathsConfig = $config['paths'] ?? [];
        assert(is_array($pathsConfig));

        // Get base path
        /** @var string $basePath */
        $basePath = $pathsConfig['base_path'] ?? dirname(__DIR__, 4);
        assert(is_string($basePath));

        // Get custom paths
        /** @var array<string, string> $customPaths */
        $customPaths = $pathsConfig['custom_paths'] ?? [];
        assert(is_array($customPaths));

        // Get preset
        /** @var string $preset */
        $preset = $pathsConfig['preset'] ?? 'mezzio';
        assert(is_string($preset));

        // Create lightweight Paths instance with preset
        $paths = Paths::withPreset($preset, $basePath, $customPaths);

        // TODO: Add security configuration support to PathsLite if needed

        return $paths;
    }

    /**
     * Create security configuration from array
     *
     * @param array<string, mixed> $securityConfig
     */
    private function createSecurityConfig(array $securityConfig): SecurityConfig
    {
        $config = SecurityConfig::forProduction();

        // Path traversal protection
        if (isset($securityConfig['path_traversal_protection'])) {
            $enabled = $securityConfig['path_traversal_protection'];
            assert(is_bool($enabled));
            $config->setPathTraversalProtection($enabled);
        }

        // Encoding protection
        if (isset($securityConfig['encoding_protection'])) {
            $enabled = $securityConfig['encoding_protection'];
            assert(is_bool($enabled));
            $config->setEncodingProtection($enabled);
        }

        // Length validation
        if (isset($securityConfig['length_validation'])) {
            $enabled = $securityConfig['length_validation'];
            assert(is_bool($enabled));
            $config->setLengthValidation($enabled);
        }

        // Max path length
        if (isset($securityConfig['max_path_length'])) {
            $maxLength = $securityConfig['max_path_length'];
            assert(is_int($maxLength));
            $config->setMaxPathLength($maxLength);
        }

        // Max filename length
        if (isset($securityConfig['max_filename_length'])) {
            $maxLength = $securityConfig['max_filename_length'];
            assert(is_int($maxLength));
            $config->setMaxFilenameLength($maxLength);
        }

        // Trusted paths
        if (isset($securityConfig['trusted_paths']) && is_array($securityConfig['trusted_paths'])) {
            $trustedPaths = [];
            foreach ($securityConfig['trusted_paths'] as $trustedPath) {
                assert(is_string($trustedPath));
                $trustedPaths[] = $trustedPath;
            }
            $config->addTrustedPaths($trustedPaths);
        }

        return $config;
    }
}
