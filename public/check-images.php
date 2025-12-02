<?php
/**
 * Image Diagnostic - Check image URLs in database
 */

require_once __DIR__ . '/../config/database-auto.php';

header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    
    // Get sample of image URLs from database
    $stmt = $conn->query("SELECT id_number, full_name, image FROM fisherfolk LIMIT 10");
    $samples = $stmt->fetchAll();
    
    $result = [
        'total_records' => 0,
        'records_with_images' => 0,
        'records_without_images' => 0,
        'sample_data' => [],
        'image_url_patterns' => []
    ];
    
    // Count total
    $countStmt = $conn->query("SELECT COUNT(*) as total FROM fisherfolk");
    $result['total_records'] = $countStmt->fetch()['total'];
    
    // Count with/without images
    $withImagesStmt = $conn->query("SELECT COUNT(*) as total FROM fisherfolk WHERE image IS NOT NULL AND image != ''");
    $result['records_with_images'] = $withImagesStmt->fetch()['total'];
    $result['records_without_images'] = $result['total_records'] - $result['records_with_images'];
    
    // Sample data
    foreach ($samples as $row) {
        $result['sample_data'][] = [
            'id' => $row['id_number'],
            'name' => $row['full_name'],
            'image_url' => $row['image'],
            'has_image' => !empty($row['image']),
            'url_length' => strlen($row['image'] ?? '')
        ];
        
        if (!empty($row['image'])) {
            // Extract pattern
            if (preg_match('#^(https?://[^/]+)#', $row['image'], $matches)) {
                $result['image_url_patterns'][] = $matches[1];
            } elseif (preg_match('#^(/[^/]+)#', $row['image'], $matches)) {
                $result['image_url_patterns'][] = $matches[1];
            }
        }
    }
    
    $result['image_url_patterns'] = array_unique($result['image_url_patterns']);
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
