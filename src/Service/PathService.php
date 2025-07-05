<?php

declare(strict_types=1);

namespace App\Service;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToReadFile;

class PathService
{
    /** @var array<string, string> */
    private array $paths;
    private Filesystem $publicFs;
    private Filesystem $themesFs;

    /**
     * @param array<string, mixed> $appConfig
     */
    public function __construct(
        array $appConfig,
        Filesystem $publicFilesystem,
        Filesystem $themesFilesystem,
        Filesystem $uploadsFilesystem // @phpstan-ignore-line unused parameter for interface compatibility
    ) {
        $paths = $appConfig['paths'] ?? [];
        assert(is_array($paths));
        $this->paths = $paths;
        $this->publicFs = $publicFilesystem;
        $this->themesFs = $themesFilesystem;
    }

    public function getRootPath(): string
    {
        return $this->paths['root'];
    }

    public function getPublicPath(): string
    {
        return $this->paths['public'];
    }

    public function getThemesPath(): string
    {
        return $this->paths['themes'];
    }

    /**
     * Bezpečne validuje cestu a vráti absolútnu cestu
     *
     * @throws \RuntimeException
     */
    public function getPublicFilePath(string $relativePath): string
    {
        return $this->validatePath($relativePath, $this->paths['public']);
    }

    /**
     * Bezpečne validuje cestu k téme
     *
     * @throws \RuntimeException
     */
    public function getThemeFilePath(string $relativePath): string
    {
        return $this->validatePath($relativePath, $this->paths['themes']);
    }

    /**
     * Bezpečne validuje cestu k upload súborom
     *
     * @throws \RuntimeException
     */
    public function getUploadFilePath(string $relativePath): string
    {
        return $this->validatePath($relativePath, $this->paths['uploads']);
    }

    /**
     * Validuje cestu proti path traversal útokom
     *
     * @throws \RuntimeException
     */
    private function validatePath(string $relativePath, string $basePath): string
    {
        // Odstráni úvodné lomítka
        $relativePath = ltrim($relativePath, '/\\');

        // Kontrola na nebezpečné znaky
        if (
            str_contains($relativePath, '..') ||
            str_contains($relativePath, '\\') ||
            preg_match('/[<>:"|?*]/', $relativePath)
        ) {
            throw new \RuntimeException('Neplatná cesta: ' . $relativePath);
        }

        // Normalizuje cestu bez použitia realpath (ktorý vyžaduje existujúci súbor)
        $fullPath = $basePath . DIRECTORY_SEPARATOR . $relativePath;
        $normalizedPath = $this->normalizePath($fullPath);
        $normalizedBasePath = $this->normalizePath($basePath);

        if (!str_starts_with($normalizedPath, $normalizedBasePath)) {
            throw new \RuntimeException('Neplatná cesta: ' . $relativePath);
        }

        return $normalizedPath;
    }

    /**
     * Normalizuje cestu bez použitia realpath
     */
    private function normalizePath(string $path): string
    {
        // Nahradí všetky lomítka jednotným separátorom
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        // Rozdelí cestu na časti
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $normalizedParts = [];

        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($normalizedParts);
            } else {
                $normalizedParts[] = $part;
            }
        }

        $result = implode(DIRECTORY_SEPARATOR, $normalizedParts);

        // Zachová úvodné lomítko pre absolútne cesty
        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            $result = DIRECTORY_SEPARATOR . $result;
        }

        return $result;
    }

    /**
     * Bezpečne číta súbor z public adresára
     *
     * @throws FilesystemException
     */
    public function readPublicFile(string $path): string
    {
        try {
            return $this->publicFs->read($path);
        } catch (UnableToReadFile $e) {
            throw new \RuntimeException('Súbor sa nedá prečítať: ' . $path, 0, $e);
        }
    }

    /**
     * Bezpečne číta súbor z themes adresára
     *
     * @throws FilesystemException
     */
    public function readThemeFile(string $path): string
    {
        try {
            return $this->themesFs->read($path);
        } catch (UnableToReadFile $e) {
            throw new \RuntimeException('Súbor sa nedá prečítať: ' . $path, 0, $e);
        }
    }

    /**
     * Kontroluje, či súbor existuje v public adresári
     */
    public function publicFileExists(string $path): bool
    {
        try {
            return $this->publicFs->fileExists($path);
        } catch (FilesystemException $e) {
            return false;
        }
    }

    /**
     * Kontroluje, či súbor existuje v themes adresári
     */
    public function themeFileExists(string $path): bool
    {
        try {
            return $this->themesFs->fileExists($path);
        } catch (FilesystemException $e) {
            return false;
        }
    }

    /**
     * Vráti bezpečnú relatívnu cestu pre URL
     */
    public function getPublicUrl(string $relativePath): string
    {
        // Validuje cestu
        $this->validatePath($relativePath, $this->paths['public']);

        // Vráti normalizovanú URL cestu
        return '/' . ltrim(str_replace('\\', '/', $relativePath), '/');
    }

    /**
     * Vráti bezpečnú relatívnu cestu pre theme URL
     */
    public function getThemeUrl(string $relativePath): string
    {
        // Validuje cestu
        $this->validatePath($relativePath, $this->paths['themes']);

        // Vráti normalizovanú URL cestu
        return '/themes/' . ltrim(str_replace('\\', '/', $relativePath), '/');
    }
}
