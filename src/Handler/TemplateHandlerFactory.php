<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\PathServiceInterface;
use Psr\Container\ContainerInterface;

class TemplateHandlerFactory
{
    public function __invoke(ContainerInterface $container): TemplateHandler
    {
        return new TemplateHandler(
            $container->get(PathServiceInterface::class)
        );
    }
}
