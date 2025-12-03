<?php
/**
 * Check specific fisherfolk image paths
 */

require_once __DIR__ . '/../config/database-auto.php';

header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    
    // Get a few records with images
    $stmt = $conn->query("SELECT id_number, full_name, image, signature FROM fisherfolk WHERE image IS NOT NULL AND image != '' LIMIT 5");
    $records = $stmt->fetchAll();
    
    $result = [];
    
    foreach ($records as $record) {
        $imagePath = $record['image'];
        $signaturePath = $record['signature'];
        
        // Build the expected web paths
        $imageWebPath = '/fisherfolk-images/' . $imagePath;
        $signatureWebPath = '/fisherfolk-images/' . $signaturePath;
        
        // Build the expected server paths
        $imageServerPath = __DIR__ . '/../fisherfolk-images/' . $imagePath;
        $signatureServerPath = __DIR__ . '/../fisherfolk-images/' . $signaturePath;
        
        $result[] = [
            'id' => $record['id_number'],
            'name' => $record['full_name'],
            'image' => [
                'filename' => $imagePath,
                'web_path' => $imageWebPath,
                'server_path' => $imageServerPath,
                'file_exists' => file_exists($imageServerPath),
                'is_readable' => file_exists($imageServerPath) ? is_readable($imageServerPath) : false
            ],
            'signature' => [
                'filename' => $signaturePath,
                'web_path' => $signatureWebPath,
                'server_path' => $signatureServerPath,
                'file_exists' => file_exists($signatureServerPath),
                'is_readable' => file_exists($signatureServerPath) ? is_readable($signatureServerPath) : false
            ]
        ];
    }
    
    // Check if fisherfolk-images directory exists
    $fisherfolkImagesDir = __DIR__ . '/../fisherfolk-images';
    $result['directory_check'] = [
        'path' => $fisherfolkImagesDir,
        'exists' => file_exists($fisherfolkImagesDir),
        'is_directory' => is_dir($fisherfolkImagesDir),
        'files_count' => is_dir($fisherfolkImagesDir) ? count(scandir($fisherfolkImagesDir)) - 2 : 0
    ];
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}
