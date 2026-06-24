<?php
/**
 * Single Asset Assign API
 *
 * Uploads ONE photo or signature for a specific fisherfolk (used to fill in
 * missing assets, or to replace an existing one). The file is stored as
 * "<id_number>.<ext>" in public/uploads/ (overwriting any existing file) and
 * the matching DB column (image | signature) is updated to that filename.
 *
 * Multipart POST fields:
 *   id_number : target record's primary key
 *   type      : "image" | "signature"
 *   file      : the uploaded image
 */

require_once __DIR__ . '/../../config/database-auto.php';
require_once __DIR__ . '/../../config/auth-functions.php';
require_once __DIR__ . '/../../config/upload-helpers.php';
require_api_auth();

setJSONHeaders();

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
    $id = isset($_POST['id_number']) ? trim($_POST['id_number']) : '';
    $type = isset($_POST['type']) ? strtolower(trim($_POST['type'])) : '';

    if ($id === '') {
        throw new Exception('Missing id_number');
    }
    if (!in_array($type, ['image', 'signature'], true)) {
        throw new Exception('type must be "image" or "signature"');
    }
    if (empty($_FILES['file'])) {
        throw new Exception('No file uploaded');
    }

    [$ok, $extOrErr] = validate_uploaded_image($_FILES['file']);
    if (!$ok) {
        throw new Exception($extOrErr);
    }
    $ext = $extOrErr;

    $conn = getDBConnection();

    // Confirm the record exists and capture any existing filename.
    $stmt = $conn->prepare('SELECT image, signature FROM fisherfolk WHERE id_number = ?');
    $stmt->execute([$id]);
    $rec = $stmt->fetch();
    if (!$rec) {
        throw new Exception('No fisherfolk found with ID ' . $id);
    }

    $targetName = asset_filename_for_id($id, $ext);
    $existed = file_exists(UPLOADS_DIR . '/' . $targetName)
        || !empty($rec[$type]);

    if (!store_uploaded_asset($_FILES['file']['tmp_name'], $targetName)) {
        throw new Exception('Could not save file to uploads directory');
    }

    // Point the DB column at the new filename.
    $upd = $conn->prepare("UPDATE fisherfolk SET $type = ? WHERE id_number = ?");
    $upd->execute([$targetName, $id]);

    echo json_encode([
        'success'  => true,
        'id_number'=> $id,
        'type'     => $type,
        'filename' => $targetName,
        'replaced' => (bool) $existed,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
