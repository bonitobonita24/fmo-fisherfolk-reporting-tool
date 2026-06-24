<?php
/**
 * Barangay Statistics API
 * Returns count of fisherfolk per barangay
 */

require_once __DIR__ . '/../../config/database-auto.php';
require_once __DIR__ . '/../../config/auth-functions.php';
require_api_auth();

setJSONHeaders();

try {
    $sql = "SELECT 
                address as barangay,
                COUNT(*) as count
            FROM fisherfolk
            GROUP BY address
            ORDER BY count DESC";
    
    $results = executeQuery($sql);
    
    echo json_encode([
        'success' => true,
        'data' => $results
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
