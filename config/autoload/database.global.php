<?php

declare(strict_types=1);

use App\Factory\DatabaseConfigFactory;

// HDM Boot Protocol - Secure Database Configuration
// SECURITY FIX: Eliminated un-secure path traversal (../../)
// Using DatabaseConfigFactory with HdmPathService for safe paths

$databaseFactory = new DatabaseConfigFactory();

return $databaseFactory->getConfig();
