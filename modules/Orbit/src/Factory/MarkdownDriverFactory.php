<?php

declare(strict_types=1);

namespace Orbit\Factory;

use Orbit\Service\FileDriver\MarkdownDriver;
use Psr\Container\ContainerInterface;

/**
 * Markdown Driver Factory
 */
class MarkdownDriverFactory
{
    public function __invoke(ContainerInterface $container): MarkdownDriver
    {
        // MarkdownDriver pracuje s absolútnymi cestami, takže nepotrebuje contentPath prefix
        return new MarkdownDriver('');
    }
}
