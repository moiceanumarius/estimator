<?php
session_start();

// Autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Initialize the application
$app = new \Core\App();
$app->run();
