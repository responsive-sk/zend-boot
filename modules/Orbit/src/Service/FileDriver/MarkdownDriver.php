<?php

declare(strict_types=1);

namespace Orbit\Service\FileDriver;

use RuntimeException;

/**
 * Markdown File Driver
 * 
 * Spracováva Markdown súbory s YAML front-matter.
 */
class MarkdownDriver implements FileDriverInterface
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

        return $this->parseContent($content);
    }

    public function write(string $path, array $data): bool
    {
        $fullPath = $this->getFullPath($path);
        $dir = dirname($fullPath);
        
        // Vytvor adresár ak neexistuje
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $content = $this->buildContent($data);
        
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
        // Základný Markdown rendering s podporou code blocks
        $html = $content;

        // Code blocks (```language ... ```)
        $html = preg_replace_callback(
            '/```(\w+)?\n(.*?)\n```/s',
            function($matches) {
                $language = $matches[1] ?: '';
                $code = htmlspecialchars($matches[2], ENT_QUOTES, 'UTF-8');
                $langClass = $language ? " class=\"language-{$language}\"" : '';
                return "<pre{$langClass}><code{$langClass}>{$code}</code></pre>";
            },
            $html
        );

        if ($html === null) {
            throw new RuntimeException("Error processing code blocks");
        }

        // Headers
        $html = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $html) ?? $html;
        $html = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $html) ?? $html;
        $html = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $html) ?? $html;

        // Bold and italic
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html) ?? $html;
        $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html) ?? $html;

        // Inline code (ale nie code blocks)
        $html = preg_replace('/`([^`\n]+)`/', '<code>$1</code>', $html) ?? $html;

        // Links
        $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $html) ?? $html;

        // Lists
        $html = preg_replace('/^- (.*$)/m', '<li>$1</li>', $html) ?? $html;
        $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html) ?? $html;

        // Line breaks (ale nie v code blocks)
        // Najprv označíme code blocks, potom aplikujeme nl2br, potom obnovíme code blocks
        $codeBlocks = [];
        $html = preg_replace_callback(
            '/<pre[^>]*>.*?<\/pre>/s',
            function($matches) use (&$codeBlocks) {
                $placeholder = "___CODE_BLOCK_" . count($codeBlocks) . "___";
                $codeBlocks[$placeholder] = $matches[0];
                return $placeholder;
            },
            $html
        );

        // Aplikuj nl2br na zvyšok obsahu
        if ($html !== null) {
            $html = nl2br($html);
        }

        // Obnov code blocks
        foreach ($codeBlocks as $placeholder => $codeBlock) {
            if ($html !== null) {
                $html = str_replace($placeholder, $codeBlock, $html);
            }
        }

        return $html ?? '';
    }

    public function getSupportedExtensions(): array
    {
        return ['md', 'markdown'];
    }

    private function getFullPath(string $path): string
    {
        // Ak je path absolútny alebo už obsahuje content path, vráť ho ako je
        if (empty($this->contentPath) || str_starts_with($path, '/') || str_starts_with($path, $this->contentPath)) {
            return $path;
        }

        return $this->contentPath . '/' . ltrim($path, '/');
    }

    private function parseContent(string $content): array
    {
        $meta = [];
        $body = $content;
        
        // Parse YAML front-matter
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $matches)) {
            $yamlContent = $matches[1];
            $body = $matches[2];
            
            // Jednoduchý YAML parser
            $meta = $this->parseYaml($yamlContent);
        }
        
        return [
            'meta' => $meta,
            'content' => trim($body),
        ];
    }

    private function buildContent(array $data): string
    {
        $content = '';
        
        // Build YAML front-matter
        if (!empty($data['meta'])) {
            $content .= "---\n";
            foreach ($data['meta'] as $key => $value) {
                if (is_array($value)) {
                    $content .= "$key:\n";
                    foreach ($value as $item) {
                        $content .= "  - \"$item\"\n";
                    }
                } elseif (is_bool($value)) {
                    $content .= "$key: " . ($value ? 'true' : 'false') . "\n";
                } elseif (is_string($value) && (strpos($value, ':') !== false || strpos($value, '"') !== false)) {
                    $content .= "$key: \"$value\"\n";
                } else {
                    $content .= "$key: $value\n";
                }
            }
            $content .= "---\n\n";
        }
        
        // Add content body
        $content .= $data['content'] ?? '';
        
        return $content;
    }

    private function parseYaml(string $yaml): array
    {
        $meta = [];
        $lines = explode("\n", $yaml);
        $currentKey = null;
        $currentArray = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }
            
            // Array items
            if (str_starts_with($line, '- ')) {
                $value = trim(substr($line, 2), '"\'');
                $currentArray[] = $value;
                continue;
            }
            
            // Finish previous array
            if ($currentKey && !empty($currentArray)) {
                $meta[$currentKey] = $currentArray;
                $currentArray = [];
                $currentKey = null;
            }
            
            // Key-value pairs
            if (preg_match('/^([^:]+):\s*(.*)$/', $line, $matches)) {
                $key = trim($matches[1]);
                $value = trim($matches[2], '"\'');
                
                if (empty($value)) {
                    // Start of array
                    $currentKey = $key;
                } else {
                    // Simple value
                    if ($value === 'true') {
                        $value = true;
                    } elseif ($value === 'false') {
                        $value = false;
                    } elseif (is_numeric($value)) {
                        $value = str_contains($value, '.') ? (float) $value : (int) $value;
                    }
                    
                    $meta[$key] = $value;
                }
            }
        }
        
        // Finish last array
        if ($currentKey && !empty($currentArray)) {
            $meta[$currentKey] = $currentArray;
        }
        
        return $meta;
    }
}
