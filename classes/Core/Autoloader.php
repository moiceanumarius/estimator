<?php

namespace Core;

class Autoloader {
    public static function register() {
        spl_autoload_register([new self(), 'loadClass']);
    }
    
    public function loadClass($className) {
        // Convert namespace to file path
        $file = __DIR__ . '/../' . str_replace('\\', '/', $className) . '.php';
        
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        
        return false;
    }
}

// Register the autoloader globally
spl_autoload_register(function($className) {
    $file = __DIR__ . '/../' . str_replace('\\', '/', $className) . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});
