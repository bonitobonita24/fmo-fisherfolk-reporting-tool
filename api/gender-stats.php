<?php
/**
 * Gender Statistics API
 * Returns count of fisherfolk by gender
 */

require_once '../config/database.php';

setJSONHeaders();

try {
    $sql = "SELECT 
                sex as gender,
                COUNT(*) as count
            FROM fisherfolk
            GROUP BY sex
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
