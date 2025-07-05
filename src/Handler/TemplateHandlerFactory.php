<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\PathServiceInterface;
use Psr\Container\ContainerInterface;

class TemplateHandlerFactory
{
    public function __invoke(ContainerInterface $container): TemplateHandler
    {
        $pathService = $container->get(PathServiceInterface::class);
        assert($pathService instanceof PathServiceInterface);
        
        return new TemplateHandler($pathService);
    }
}
