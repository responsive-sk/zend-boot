<?php

declare(strict_types=1);

namespace App\Template;

use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Template\TemplatePath;
use Psr\SimpleCache\CacheInterface;

class PhpRenderer implements TemplateRendererInterface
{
    /** @var array<string, array<string>> */
    private array $paths = [];
    /** @var array<string, mixed> */
    private array $defaultParams = [];
    private ?CacheInterface $cache = null;
    private bool $cacheEnabled = false;
    private int $cacheTtl = 3600;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        if (isset($config['paths']) && is_array($config['paths'])) {
            $this->paths = $config['paths'];
        }
    }

    /**
     * Set cache service for template caching
     */
    public function setCache(CacheInterface $cache, int $ttl = 3600): void
    {
        $this->cache = $cache;
        $this->cacheTtl = $ttl;
    }

    /**
     * Enable or disable template caching
     */
    public function enableCache(bool $enabled = true): void
    {
        $this->cacheEnabled = $enabled && $this->cache !== null;
    }

    /**
     * @param mixed $params
     */
    public function render(string $name, $params = []): string
    {
        // Ensure params is array
        if (!is_array($params)) {
            $params = [];
        }

        $defaultParams = $this->defaultParams;
        $params = array_merge($defaultParams, $params);

        // Check cache if enabled
        if ($this->cacheEnabled && $this->cache) {
            $cacheKey = $this->getCacheKey($name, $params);
            $cachedContent = $this->cache->get($cacheKey);

            if ($cachedContent !== null) {
                return $cachedContent;
            }
        }

        // Parse template name (namespace::template)
        if (strpos($name, '::') !== false) {
            [$namespace, $template] = explode('::', $name, 2);
            $templatePath = $this->findTemplate($namespace, $template);
        } else {
            $templatePath = $this->findTemplate('', $name);
        }

        if (!$templatePath || !file_exists($templatePath)) {
            throw new \RuntimeException("Template '{$name}' not found");
        }

        $content = $this->renderTemplate($templatePath, $params);

        // Store in cache if enabled
        if ($this->cacheEnabled && $this->cache) {
            $cacheKey = $this->getCacheKey($name, $params);
            $this->cache->set($cacheKey, $content, $this->cacheTtl);
        }

        return $content;
    }

    public function addPath(string $path, ?string $namespace = null): void
    {
        $namespace = $namespace ?: '';
        if (!isset($this->paths[$namespace])) {
            $this->paths[$namespace] = [];
        }
        $this->paths[$namespace][] = rtrim($path, '/');
    }

    /**
     * @return TemplatePath[]
     */
    public function getPaths(): array
    {
        $templatePaths = [];
        foreach ($this->paths as $namespace => $paths) {
            foreach ($paths as $path) {
                $templatePaths[] = new TemplatePath($path, $namespace === '' ? null : $namespace);
            }
        }
        return $templatePaths;
    }

    /**
     * @param mixed $value
     */
    public function addDefaultParam(string $templateName, string $param, $value): void
    {
        if (!isset($this->defaultParams[$templateName])) {
            $this->defaultParams[$templateName] = [];
        }
        if (!is_array($this->defaultParams[$templateName])) {
            $this->defaultParams[$templateName] = [];
        }
        $this->defaultParams[$templateName][$param] = $value;
    }

    /**
     * Clear template cache for specific template
     */
    public function clearTemplateCache(string $name, array $params = []): bool
    {
        if ($this->cacheEnabled && $this->cache) {
            $cacheKey = $this->getCacheKey($name, $params);
            return $this->cache->delete($cacheKey);
        }

        return false;
    }

    /**
     * Clear all template cache
     */
    public function clearAllCache(): bool
    {
        if ($this->cacheEnabled && $this->cache) {
            return $this->cache->clear();
        }

        return false;
    }

    private function findTemplate(string $namespace, string $template): ?string
    {
        $paths = $this->paths[$namespace] ?? $this->paths[''] ?? [];

        foreach ($paths as $path) {
            assert(is_string($path));
            $templatePath = $path . '/' . $template;

            // Try with .phtml extension if not provided
            if (!pathinfo($templatePath, PATHINFO_EXTENSION)) {
                $templatePath .= '.phtml';
            }

            if (file_exists($templatePath)) {
                return $templatePath;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $params
     */
    private function renderTemplate(string $templatePath, array $params): string
    {
        // Extract variables for template
        extract($params, EXTR_SKIP);

        // Helper functions for templates
        $escapeHtml = function ($value): string {
            return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        };

        ob_start();
        include $templatePath;
        $content = ob_get_clean();

        if ($content === false) {
            throw new \RuntimeException('Failed to capture template output');
        }

        return $content;
    }

    private function getCacheKey(string $name, array $params): string
    {
        $paramHash = md5(serialize($params));
        $templateHash = md5($name . serialize($this->paths));
        return "template.{$templateHash}.{$paramHash}";
    }
}