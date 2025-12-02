<?php
/**
 * Comprehensive Production Diagnostic
 */

header('Content-Type: application/json');

$diagnostic = [
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => [],
    'file_checks' => [],
    'database_test' => []
];

// Server info
$diagnostic['server_info'] = [
    'php_version' => phpversion(),
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'not set',
    'script_filename' => __FILE__,
    'http_host' => $_SERVER['HTTP_HOST'] ?? 'not set',
    'hostname' => gethostname()
];

// Check if config files exist
$configFiles = [
    'database-auto.php' => __DIR__ . '/../config/database-auto.php',
    'env.php' => __DIR__ . '/../config/env.php',
    'database.php' => __DIR__ . '/../config/database.php',
    'database.prod.php' => __DIR__ . '/../config/database.prod.php'
];

foreach ($configFiles as $name => $path) {
    $diagnostic['file_checks'][$name] = [
        'path' => $path,
        'exists' => file_exists($path),
        'readable' => file_exists($path) ? is_readable($path) : false
    ];
}

// Check API files
$apiFiles = [
    'summary-stats.php',
    'barangay-fisherfolk-list.php',
    'gender-stats.php'
];

foreach ($apiFiles as $file) {
    $path = __DIR__ . '/../api/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $usesAutoDb = strpos($content, 'database-auto.php') !== false;
        $usesEnv = strpos($content, 'env.php') !== false;
        $usesDb = strpos($content, "config/database.php'") !== false;
        
        $diagnostic['file_checks']['api_' . $file] = [
            'exists' => true,
            'uses_database_auto' => $usesAutoDb,
            'uses_env' => $usesEnv,
            'uses_old_database' => $usesDb
        ];
    }
}

// Test database connection
try {
    if (file_exists(__DIR__ . '/../config/database-auto.php')) {
        require_once __DIR__ . '/../config/database-auto.php';
        
        $diagnostic['database_test']['config_loaded'] = true;
        $diagnostic['database_test']['constants'] = [
            'DB_HOST' => defined('DB_HOST') ? DB_HOST : 'not defined',
            'DB_NAME' => defined('DB_NAME') ? DB_NAME : 'not defined',
            'DB_USER' => defined('DB_USER') ? DB_USER : 'not defined'
        ];
        
        try {
            $conn = getDBConnection();
            $diagnostic['database_test']['connection'] = 'success';
            
            // Test query
            $stmt = $conn->query("SELECT COUNT(*) as total FROM fisherfolk LIMIT 1");
            $result = $stmt->fetch();
            $diagnostic['database_test']['record_count'] = $result['total'];
            
        } catch (Exception $e) {
            $diagnostic['database_test']['connection'] = 'failed';
            $diagnostic['database_test']['error'] = $e->getMessage();
        }
    } else {
        $diagnostic['database_test']['config_loaded'] = false;
        $diagnostic['database_test']['error'] = 'database-auto.php not found';
    }
} catch (Exception $e) {
    $diagnostic['database_test']['error'] = $e->getMessage();
}

echo json_encode($diagnostic, JSON_PRETTY_PRINT);
