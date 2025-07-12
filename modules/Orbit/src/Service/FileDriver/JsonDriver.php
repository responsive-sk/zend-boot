<?php

declare(strict_types=1);

namespace Orbit\Service\FileDriver;

use RuntimeException;

/**
 * JSON File Driver
 * 
 * Spracováva JSON súbory.
 */
class JsonDriver implements FileDriverInterface
{
    private string $contentPath;

    public function __construct(string $contentPath = 'content')
    {
        $this->contentPath = rtrim($contentPath, '/');
    }

    public function read(string $path): array
    {
        $fullPath = $this->getFullPath($path);
        
        if (!$this->exists($path)) {
            throw new RuntimeException("File not found: $fullPath");
        }
        
        $content = file_get_contents($fullPath);
        if ($content === false) {
            throw new RuntimeException("Could not read file: $fullPath");
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid JSON in file: $fullPath");
        }

        assert(is_array($data));

        return [
            'meta' => $data['meta'] ?? [],
            'content' => $data['content'] ?? '',
        ];
    }

    public function write(string $path, array $data): bool
    {
        $fullPath = $this->getFullPath($path);
        $dir = dirname($fullPath);
        
        // Vytvor adresár ak neexistuje
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $jsonData = [
            'meta' => $data['meta'] ?? [],
            'content' => $data['content'] ?? '',
        ];
        
        $content = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        return file_put_contents($fullPath, $content) !== false;
    }

    public function exists(string $path): bool
    {
        return file_exists($this->getFullPath($path));
    }

    public function delete(string $path): bool
    {
        $fullPath = $this->getFullPath($path);
        
        if (!$this->exists($path)) {
            return true;
        }
        
        return unlink($fullPath);
    }

    public function render(string $content): string
    {
        // JSON content je už text, len ho vrátime
        return nl2br(htmlspecialchars($content));
    }

    public function getSupportedExtensions(): array
    {
        return ['json'];
    }

    private function getFullPath(string $path): string
    {
        // Ak path už obsahuje content path, neprida ho znovu
        if (str_starts_with($path, $this->contentPath)) {
            return $path;
        }
        
        return $this->contentPath . '/' . ltrim($path, '/');
    }
}
