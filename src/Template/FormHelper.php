<?php

declare(strict_types=1);

namespace App\Template;

/**
 * Simple form helper for templates
 */
class FormHelper
{
    /**
     * @param mixed $form
     */
    public function openTag($form): string
    {
        $attributes = $form->getAttributes();
        $method = $attributes['method'] ?? 'POST';
        $action = $attributes['action'] ?? '';
        $class = $attributes['class'] ?? '';

        return sprintf(
            '<form method="%s" action="%s" class="%s">',
            htmlspecialchars($method),
            htmlspecialchars($action),
            htmlspecialchars($class)
        );
    }

    public function closeTag(): string
    {
        return '</form>';
    }
}
