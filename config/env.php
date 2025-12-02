<?php
/**
 * Environment Configuration Loader
 * Automatically loads the correct database configuration based on environment
 */

// Detect environment based on hostname or environment variable
function getEnvironment() {
    // Check if environment is set via environment variable
    if (isset($_ENV['APP_ENV'])) {
        return $_ENV['APP_ENV'];
    }
    
    // Check via server hostname
    $hostname = gethostname();
    
    // Development hostname detection (localhost or specific dev markers)
    if (strpos($hostname, 'localhost') !== false || 
        strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
        strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false) {
        return 'development';
    }
    
    // Default to production for shared hosting
    return 'production';
}

// Load appropriate configuration
$environment = getEnvironment();

if ($environment === 'production') {
    require_once __DIR__ . '/database.prod.php';
} else {
    require_once __DIR__ . '/database.php';
}

// Set environment constant for use throughout the application
define('APP_ENV', $environment);
