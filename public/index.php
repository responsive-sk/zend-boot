<?php

declare(strict_types=1);

use Mezzio\Application;
use Psr\Container\ContainerInterface;

// Delegate static file requests back to the PHP built-in webserver
if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
    return false;
}

chdir(dirname(__DIR__));

// Auto-create var/ structure for shared hosting compatibility
$varDirs = ['var', 'var/data', 'var/cache', 'var/logs', 'var/tmp', 'var/sessions'];
foreach ($varDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

require 'vendor/autoload.php';

/**
 * Self-called anonymous function that creates its own scope and keep the global namespace clean.
 */
(function () {
    /** @var ContainerInterface $container */
    $container = require 'config/container.php';

    /** @var Application $app */
    $app = $container->get(Application::class);

    // Execute programmatic/declarative middleware pipeline and routing configuration statements
    $pipeline = require 'config/pipeline.php';
    assert(is_callable($pipeline));
    $pipeline($app);

    $app->run();
})();
