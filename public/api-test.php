<?php
/**
 * API Diagnostic Test Script
 * Use this to test database connection and API endpoints on production
 */

// Load environment configuration
require_once __DIR__ . '/../config/env.php';

header('Content-Type: application/json');

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'environment' => getEnvironment(),
    'tests' => []
];

// Test 1: Check environment detection
$results['tests']['environment_detection'] = [
    'status' => 'success',
    'detected_env' => getEnvironment(),
    'hostname' => gethostname(),
    'http_host' => $_SERVER['HTTP_HOST'] ?? 'not set',
    'app_env_var' => $_ENV['APP_ENV'] ?? 'not set'
];

// Test 2: Database connection
try {
    $conn = getDBConnection();
    $results['tests']['database_connection'] = [
        'status' => 'success',
        'message' => 'Database connected successfully',
        'host' => DB_HOST,
        'database' => DB_NAME
    ];
    
    // Test 3: Check if fisherfolk table exists
    try {
        $stmt = $conn->query("SHOW TABLES LIKE 'fisherfolk'");
        $tableExists = $stmt->rowCount() > 0;
        
        $results['tests']['table_exists'] = [
            'status' => $tableExists ? 'success' : 'error',
            'message' => $tableExists ? 'fisherfolk table exists' : 'fisherfolk table not found'
        ];
        
        if ($tableExists) {
            // Test 4: Count records
            $stmt = $conn->query("SELECT COUNT(*) as total FROM fisherfolk");
            $count = $stmt->fetch();
            
            $results['tests']['record_count'] = [
                'status' => 'success',
                'total_records' => (int)$count['total']
            ];
            
            // Test 5: Sample record
            $stmt = $conn->query("SELECT * FROM fisherfolk LIMIT 1");
            $sample = $stmt->fetch();
            
            $results['tests']['sample_record'] = [
                'status' => 'success',
                'has_data' => !empty($sample),
                'columns' => $sample ? array_keys($sample) : []
            ];
        }
    } catch (PDOException $e) {
        $results['tests']['table_check'] = [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
    
} catch (Exception $e) {
    $results['tests']['database_connection'] = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Test 6: Check API endpoint accessibility
$results['tests']['api_endpoints'] = [
    'summary-stats' => file_exists(__DIR__ . '/../api/summary-stats.php'),
    'barangay-fisherfolk-list' => file_exists(__DIR__ . '/../api/barangay-fisherfolk-list.php'),
    'barangay-category-stats' => file_exists(__DIR__ . '/../api/barangay-category-stats.php')
];

// Output results
echo json_encode($results, JSON_PRETTY_PRINT);
