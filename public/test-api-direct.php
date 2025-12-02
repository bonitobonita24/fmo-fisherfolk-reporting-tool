<?php
/**
 * Direct API Test - Tests actual API endpoints
 */

header('Content-Type: application/json');

$tests = [];

// Test each API endpoint directly
$endpoints = [
    'summary-stats',
    'gender-stats',
    'age-group-stats',
    'barangay-stats',
    'category-stats',
    'barangay-category-stats',
    'barangay-list',
    'barangay-fisherfolk-list'
];

foreach ($endpoints as $endpoint) {
    $filePath = __DIR__ . "/../api/{$endpoint}.php";
    
    if (file_exists($filePath)) {
        ob_start();
        try {
            include $filePath;
            $output = ob_get_clean();
            $decoded = json_decode($output, true);
            
            $tests[$endpoint] = [
                'status' => 'success',
                'file_exists' => true,
                'output_valid_json' => $decoded !== null,
                'data_count' => is_array($decoded) ? count($decoded) : 'N/A',
                'first_item' => is_array($decoded) && !empty($decoded) ? (is_array($decoded[0]) ? 'array' : $decoded[0]) : 'N/A'
            ];
        } catch (Exception $e) {
            ob_end_clean();
            $tests[$endpoint] = [
                'status' => 'error',
                'file_exists' => true,
                'error' => $e->getMessage()
            ];
        }
    } else {
        $tests[$endpoint] = [
            'status' => 'error',
            'file_exists' => false,
            'message' => 'File not found'
        ];
    }
}

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => $tests
], JSON_PRETTY_PRINT);
