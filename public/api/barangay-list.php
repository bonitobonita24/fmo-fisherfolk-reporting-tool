<?php
/**
 * Barangay List API
 * Returns list of all barangays
 */

require_once __DIR__ . '/../../config/database-auto.php';

setJSONHeaders();

try {
    $sql = "SELECT DISTINCT address as barangay
            FROM fisherfolk
            ORDER BY address ASC";
    
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
