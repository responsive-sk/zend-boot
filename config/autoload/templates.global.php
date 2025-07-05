<?php

declare(strict_types=1);

use App\Factory\TemplateConfigFactory;

// HDM Boot Protocol - Secure Template Configuration
// SECURITY FIX: Eliminated un-secure path traversal (../../)
// Using TemplateConfigFactory with HdmPathService for safe paths

$templateFactory = new TemplateConfigFactory();

return $templateFactory->getConfig();
