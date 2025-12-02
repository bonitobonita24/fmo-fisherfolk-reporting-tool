<?php
/**
 * CSV Import API
 * Imports fisherfolk data from CSV
 */

require_once __DIR__ . '/../config/database.php';

setJSONHeaders();

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $requestData = json_decode($input, true);
    
    if (!isset($requestData['data']) || !is_array($requestData['data'])) {
        throw new Exception('Invalid request data');
    }
    
    $data = $requestData['data'];
    $conn = getDBConnection();
    
    $imported = 0;
    $skipped = 0;
    $errors = [];
    
    // Prepare insert statement
    $sql = "INSERT INTO fisherfolk (
        id_number, full_name, date_of_birth, address, sex, 
        image, signature, rsbsa, contact_number,
        boat_owneroperator, capture_fishing, gleaning, 
        vendor, fish_processing, aquaculture
    ) VALUES (
        :id_number, :full_name, :date_of_birth, :address, :sex,
        :image, :signature, :rsbsa, :contact_number,
        :boat_owneroperator, :capture_fishing, :gleaning,
        :vendor, :fish_processing, :aquaculture
    )";
    
    $stmt = $conn->prepare($sql);
    
    foreach ($data as $index => $row) {
        try {
            // Validate required fields
            if (empty($row['id_number']) || empty($row['full_name'])) {
                $errors[] = "Row " . ($index + 1) . ": Missing required fields (id_number or full_name)";
                $skipped++;
                continue;
            }
            
            // Check if ID already exists
            $checkSql = "SELECT COUNT(*) as count FROM fisherfolk WHERE id_number = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([$row['id_number']]);
            $exists = $checkStmt->fetch()['count'] > 0;
            
            if ($exists) {
                $skipped++;
                continue;
            }
            
            // Convert date from DD/MM/YYYY to YYYY-MM-DD
            $dateOfBirth = null;
            if (!empty($row['date_of_birth'])) {
                $dateParts = explode('/', $row['date_of_birth']);
                if (count($dateParts) === 3) {
                    // DD/MM/YYYY to YYYY-MM-DD
                    $dateOfBirth = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                }
            }
            
            // Clean and validate data
            $params = [
                ':id_number' => $row['id_number'],
                ':full_name' => $row['full_name'],
                ':date_of_birth' => $dateOfBirth,
                ':address' => $row['address'] ?? '',
                ':sex' => $row['sex'] ?? '',
                ':image' => $row['image'] ?? null,
                ':signature' => $row['signature'] ?? null,
                ':rsbsa' => $row['rsbsa'] ?? null,
                ':contact_number' => $row['contact_number'] ?? null,
                ':boat_owneroperator' => isset($row['boat_owneroperator']) ? (int)$row['boat_owneroperator'] : 0,
                ':capture_fishing' => isset($row['capture_fishing']) ? (int)$row['capture_fishing'] : 0,
                ':gleaning' => isset($row['gleaning']) ? (int)$row['gleaning'] : 0,
                ':vendor' => isset($row['vendor']) ? (int)$row['vendor'] : 0,
                ':fish_processing' => isset($row['fish_processing']) ? (int)$row['fish_processing'] : 0,
                ':aquaculture' => isset($row['aquaculture']) ? (int)$row['aquaculture'] : 0
            ];
            
            $stmt->execute($params);
            $imported++;
            
        } catch (PDOException $e) {
            $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
            $skipped++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'imported' => $imported,
        'skipped' => $skipped,
        'total' => count($data),
        'errors' => $errors
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
