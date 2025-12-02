<?php
/**
 * Barangay Activity Category Statistics API
 * Returns count of fisherfolk by activity category for a specific barangay
 */

require_once __DIR__ . '/../config/database-auto.php';

setJSONHeaders();

try {
    // Get barangay from query parameter, default to all
    $barangay = isset($_GET['barangay']) ? $_GET['barangay'] : 'all';
    
    $conn = getDBConnection();
    
    // Build query based on barangay filter
    if ($barangay !== 'all') {
        // Query for specific barangay
        $sql = "SELECT 
                    'Boat Owner/Operator' as category,
                    SUM(boat_owneroperator) as count
                FROM fisherfolk
                WHERE address = ?
                UNION ALL
                SELECT 
                    'Capture Fishing' as category,
                    SUM(capture_fishing) as count
                FROM fisherfolk
                WHERE address = ?
                UNION ALL
                SELECT 
                    'Gleaning' as category,
                    SUM(gleaning) as count
                FROM fisherfolk
                WHERE address = ?
                UNION ALL
                SELECT 
                    'Vendor' as category,
                    SUM(vendor) as count
                FROM fisherfolk
                WHERE address = ?
                UNION ALL
                SELECT 
                    'Fish Processing' as category,
                    SUM(fish_processing) as count
                FROM fisherfolk
                WHERE address = ?
                UNION ALL
                SELECT 
                    'Aquaculture' as category,
                    SUM(aquaculture) as count
                FROM fisherfolk
                WHERE address = ?
                ORDER BY count DESC";
        
        $stmt = $conn->prepare($sql);
        // Bind the same barangay value 6 times (once for each UNION)
        $stmt->execute([$barangay, $barangay, $barangay, $barangay, $barangay, $barangay]);
    } else {
        // Query for all barangays
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
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }
    
    $results = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'barangay' => $barangay,
        'data' => $results
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
