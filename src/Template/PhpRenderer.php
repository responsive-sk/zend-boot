<?php

declare(strict_types=1);

namespace App\Template;

use Mezzio\Template\TemplateRendererInterface;

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
        if (isset($config['paths'])) {
            $this->paths = $config['paths'];
        }
    }

    /**
     * @param array<string, mixed> $params
     */
    public function render(string $name, $params = []): string
    {
        $params = array_merge($this->defaultParams, $params);

        // Parse template name (namespace::template)
        if (strpos($name, '::') !== false) {
            [$namespace, $template] = explode('::', $name, 2);
            $templatePath = $this->findTemplate($namespace, $template);
        } else {
            $templatePath = $this->findTemplate('', $name);
        }

        if (!$templatePath || !file_exists($templatePath)) {
            throw new \RuntimeException("Template not found: {$name}");
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

    public function getPaths(): array
    {
        return $this->paths;
    }

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
        return ob_get_clean();
    }
}
