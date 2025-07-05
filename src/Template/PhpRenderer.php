<?php

declare(strict_types=1);

namespace App\Template;

use App\Template\FormHelper;
use Mezzio\Template\TemplatePath;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Simple PHP template renderer with security improvements
 */
class PhpRenderer implements TemplateRendererInterface
{
    /** @var array<string, array<string>> */
    private array $paths = [];

    /** @var array<string, mixed> */

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        // Constructor can accept config but doesn't need to use it
        // Paths are added via addPath() method in factory
    }
    private array $defaultParams = [];

    public function addPath(string $path, ?string $namespace = null): void
    {
        $namespace = $namespace ?? '';
        if (!isset($this->paths[$namespace])) {
            $this->paths[$namespace] = [];
        }
        $this->paths[$namespace][] = rtrim($path, '/');
    }

    /**
     * @return array<string, array<string>>
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function render(string $name, $params = []): string
    {
        $templatePath = $this->resolveTemplate($name);
        if (!$templatePath) {
            throw new \RuntimeException("Template '{$name}' not found");
        }

        // Merge with default parameters
        $namespace = '';
        if (str_contains($name, '::')) {
            [$namespace] = explode('::', $name, 2);
        }
        
        $defaultParams = $this->defaultParams[$namespace] ?? [];
        $params = array_merge($defaultParams, $params);

        return $this->renderTemplate($templatePath, $params);
    }

    private function resolveTemplate(string $name): ?string
    {
        if (str_contains($name, '::')) {
            [$namespace, $template] = explode('::', $name, 2);
            return $this->findTemplate($namespace, $template);
        }

        return $this->findTemplate('', $name);
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
        $content = ob_get_clean();

        if ($content === false) {
            throw new \RuntimeException('Failed to capture template output');
        }

        return $content;
    }

    public function addDefaultParam(string $templateName, string $param, mixed $value): void
    {
        if (!isset($this->defaultParams[$templateName]) || !is_array($this->defaultParams[$templateName])) {
            $this->defaultParams[$templateName] = [];
        }
        $this->defaultParams[$templateName][$param] = $value;
    }

    /**
     * Escape HTML for safe output in templates
     */
    public function escapeHtml(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Get form helper for templates
     */
    public function form(): FormHelper
    {
        return new FormHelper();
    }

    /**
     * Form label helper with security improvements
     */
    public function formLabel(mixed $element): string
    {
        if (!is_object($element) || !method_exists($element, 'getLabel')) {
            return '';
        }
        
        $label = $element->getLabel();
        $name = is_object($element) && method_exists($element, 'getName') 
            ? $element->getName() 
            : '';
        
        return sprintf('<label for="%s">%s</label>', 
            $this->escapeHtml($name), 
            $this->escapeHtml($label)
        );
    }

    /**
     * Form element helper with XSS protection
     */
    public function formElement(mixed $element): string
    {
        if (!is_object($element) || !method_exists($element, 'getAttributes')) {
            return '';
        }
        
        $attributes = $element->getAttributes();
        $type = $attributes['type'] ?? 'text';
        $name = $attributes['name'] ?? '';
        $value = $attributes['value'] ?? '';
        $class = $attributes['class'] ?? 'form-control';
        
        $attrs = [
            'type' => $this->escapeHtml($type),
            'name' => $this->escapeHtml($name),
            'class' => $this->escapeHtml($class),
        ];
        
        if ($value) {
            $attrs['value'] = $this->escapeHtml($value);
        }
        if (!empty($attributes['required'])) {
            $attrs['required'] = 'required';
        }
        if (!empty($attributes['placeholder'])) {
            $attrs['placeholder'] = $this->escapeHtml($attributes['placeholder']);
        }
        
        $attrString = '';
        foreach ($attrs as $k => $v) {
            $attrString .= sprintf(' %s="%s"', $k, $v);
        }
        
        
        // Debug: Log what we're looking for
        error_log("Looking for template: namespace='$namespace', template='$template'");
        error_log("Available paths for namespace '$namespace': " . json_encode($paths));
        error_log("All paths: " . json_encode($this->paths));
        if ($type === 'submit') {
            return sprintf('<button type="submit"%s>%s</button>', 
                $attrString,
                $this->escapeHtml($value ?: 'Submit')
            );
        }
        
        return sprintf('<input%s>', $attrString);
    }

    /**
     * Form element errors helper
     */
    public function formElementErrors(mixed $element, array $attributes = []): string
    {
        // Basic implementation - return empty for now
        return '';
    }

    /**
     * Secure template finder with path traversal protection
     */
    private function findTemplate(string $namespace, string $template): ?string
    {
        $paths = $this->paths[$namespace] ?? $this->paths[''] ?? [];
        
        foreach ($paths as $path) {
            $templatePath = realpath($path . '/' . ltrim($template, '/'));
            
            if ($templatePath === false) {
                continue;
            }
            
            // Check if template is in allowed directory
            $allowed = false;
            foreach ($paths as $allowedPath) {
                $realAllowedPath = realpath($allowedPath);
                if ($realAllowedPath && str_starts_with($templatePath, $realAllowedPath)) {
                    $allowed = true;
                    break;
                }
            }
            
            if (!$allowed) {
                continue;
            }
            
            if (!pathinfo($templatePath, PATHINFO_EXTENSION)) {
                $templatePath .= '.phtml';
            }
            
            if (file_exists($templatePath)) {
                return $templatePath;
            }
        }
        
        return null;
    }
}
