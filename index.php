<?php
session_start();

// Autoloader - use absolute path for production server
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    // Fallback for production server where project is in /var/www/estimator
    $autoloadPath = '/var/www/estimator/vendor/autoload.php';
}
require_once $autoloadPath;

// Initialize the application
$app = new \Core\App();
$app->run();
