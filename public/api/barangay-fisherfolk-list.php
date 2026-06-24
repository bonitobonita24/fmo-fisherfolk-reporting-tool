<?php
/**
 * Barangay Fisherfolk List API
 * Returns list of fisherfolk for a specific barangay with their details
 */

require_once __DIR__ . '/../../config/database-auto.php';
require_once __DIR__ . '/../../config/auth-functions.php';
require_api_auth();

setJSONHeaders();

try {
    // Get barangay from query parameter, default to all
    $barangay = isset($_GET['barangay']) ? $_GET['barangay'] : 'all';
    
    $conn = getDBConnection();
    
    // Build query based on barangay filter
    if ($barangay !== 'all') {
        $sql = "SELECT 
                    id_number,
                    full_name,
                    address,
                    sex,
                    date_of_birth,
                    contact_number,
                    rsbsa,
                    image,
                    signature,
                    boat_owneroperator,
                    capture_fishing,
                    gleaning,
                    vendor,
                    fish_processing,
                    aquaculture
                FROM fisherfolk
                WHERE address = ?
                ORDER BY full_name ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$barangay]);
    } else {
        $sql = "SELECT 
                    id_number,
                    full_name,
                    address,
                    sex,
                    date_of_birth,
                    contact_number,
                    rsbsa,
                    image,
                    signature,
                    boat_owneroperator,
                    capture_fishing,
                    gleaning,
                    vendor,
                    fish_processing,
                    aquaculture
                FROM fisherfolk
                ORDER BY address ASC, full_name ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }
    
    $results = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'barangay' => $barangay,
        'count' => count($results),
        'data' => $results
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
