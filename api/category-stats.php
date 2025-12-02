<?php
/**
 * Category Statistics API
 * Returns fisherfolk count by activity category
 */

require_once __DIR__ . '/../config/env.php';

setJSONHeaders();

try {
    $sql = "SELECT 
                'Boat Owner/Operator' as category,
                SUM(boat_owneroperator) as count
            FROM fisherfolk
            UNION ALL
            SELECT 
                'Capture Fishing' as category,
                SUM(capture_fishing) as count
            FROM fisherfolk
            UNION ALL
            SELECT 
                'Gleaning' as category,
                SUM(gleaning) as count
            FROM fisherfolk
            UNION ALL
            SELECT 
                'Vendor' as category,
                SUM(vendor) as count
            FROM fisherfolk
            UNION ALL
            SELECT 
                'Fish Processing' as category,
                SUM(fish_processing) as count
            FROM fisherfolk
            UNION ALL
            SELECT 
                'Aquaculture' as category,
                SUM(aquaculture) as count
            FROM fisherfolk
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
