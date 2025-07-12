<?php

declare(strict_types=1);

/**
 * HDM Boot Protocol - Application Entry Point
 *
 * Clean, minimal entry point that delegates all logic to boot system
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Boot\ApplicationFactory;

// Initialize paths configuration
$paths = require dirname(__DIR__) . '/config/paths.php';

// Create and run application
$app = ApplicationFactory::create();
$app->run();
