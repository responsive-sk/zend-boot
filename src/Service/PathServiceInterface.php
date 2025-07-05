<?php

declare(strict_types=1);

namespace App\Service;

/**
 * HDM Boot Protocol - Path Service Interface
 * 
 * Common interface for all path services
 */
interface PathServiceInterface
{
    // Legacy PathService methods
    public function getRootPath(): string;
    public function getPublicPath(): string;
    public function getThemesPath(): string;
    public function getPublicFilePath(string $relativePath): string;
    public function getThemeFilePath(string $relativePath): string;
    public function getUploadFilePath(string $relativePath): string;
    public function readPublicFile(string $path): string;
    public function readThemeFile(string $path): string;
    public function publicFileExists(string $path): bool;
    public function themeFileExists(string $path): bool;
    public function getPublicUrl(string $relativePath): string;
    public function getThemeUrl(string $relativePath): string;

    // HDM Boot Protocol methods
    public function storage(string $filename = ''): string;
    public function logs(string $filename = ''): string;
    public function cache(string $filename = ''): string;
    public function sessions(string $filename = ''): string;
    public function uploads(string $filename = ''): string;
    public function path(string $relativePath): string;
}
