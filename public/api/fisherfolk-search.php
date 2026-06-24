<?php
/**
 * Fisherfolk Search API
 * Lightweight typeahead search by id_number or full_name, used by the
 * "single asset fix" picker on the Manage Data page. Also flags which assets
 * (photo / signature) are currently missing for each match.
 *
 * GET ?q=<term>&limit=<n>
 */

require_once __DIR__ . '/../../config/database-auto.php';
require_once __DIR__ . '/../../config/auth-functions.php';
require_api_auth();

setJSONHeaders();

try {
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $limit = isset($_GET['limit']) ? max(1, min(50, (int) $_GET['limit'])) : 20;

    if ($q === '') {
        echo json_encode(['success' => true, 'count' => 0, 'data' => []]);
        exit;
    }

    $conn = getDBConnection();
    $like = '%' . $q . '%';
    $sql = 'SELECT id_number, full_name, address, sex, image, signature
            FROM fisherfolk
            WHERE id_number LIKE ? OR full_name LIKE ?
            ORDER BY full_name ASC
            LIMIT ' . $limit;
    $stmt = $conn->prepare($sql);
    $stmt->execute([$like, $like]);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$r) {
        $r['has_image'] = !empty($r['image']);
        $r['has_signature'] = !empty($r['signature']);
    }

    echo json_encode(['success' => true, 'count' => count($rows), 'data' => $rows]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
