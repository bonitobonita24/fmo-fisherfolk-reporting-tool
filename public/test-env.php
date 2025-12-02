<?php
/**
 * Environment Detection Test
 */

header('Content-Type: application/json');

// Test 1: Check what environment is detected
function getEnvironment() {
    if (isset($_ENV['APP_ENV'])) {
        return $_ENV['APP_ENV'];
    }
    
    $hostname = gethostname();
    
    if (strpos($hostname, 'localhost') !== false || 
        strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
        strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false) {
        return 'development';
    }
    
    return 'production';
}

$env = getEnvironment();

$result = [
    'detected_environment' => $env,
    'hostname' => gethostname(),
    'http_host' => $_SERVER['HTTP_HOST'] ?? 'not set',
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'not set',
    'app_env_variable' => $_ENV['APP_ENV'] ?? 'not set',
    'should_use_file' => $env === 'production' ? 'config/database.prod.php' : 'config/database.php'
];

// Now load env.php and check what constants are set
require_once __DIR__ . '/../config/env.php';

$result['loaded_constants'] = [
    'DB_HOST' => defined('DB_HOST') ? DB_HOST : 'not defined',
    'DB_NAME' => defined('DB_NAME') ? DB_NAME : 'not defined',
    'DB_USER' => defined('DB_USER') ? DB_USER : 'not defined',
    'APP_ENV' => defined('APP_ENV') ? APP_ENV : 'not defined'
];

// Test connection
try {
    $conn = getDBConnection();
    $result['connection_test'] = [
        'status' => 'success',
        'message' => 'Connected successfully'
    ];
} catch (Exception $e) {
    $result['connection_test'] = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT);
