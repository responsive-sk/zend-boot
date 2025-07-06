<?php

declare(strict_types=1);

namespace App\Template;

use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Template\TemplatePath;

class PhpRenderer implements TemplateRendererInterface
{
    /** @var array<string, array<string>> */
    private array $paths = [];
    /** @var array<string, mixed> */
    private array $defaultParams = [];

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
     * @param array<string, mixed> $params
     */
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
        assert(is_array($defaultParams));
        $params = array_merge($defaultParams, $params);

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

        return $this->renderTemplate($templatePath, $params);
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
     * @param $value
     */
    public function addDefaultParam(string $templateName, string $param, $value): void
    {
        if (!isset($this->defaultParams[$templateName])) {
            $this->defaultParams[$templateName] = [];
        }
        $this->defaultParams[$templateName][$param] = $value;
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
}
