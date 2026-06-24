<?php
/**
 * Bulk Data Import API
 *
 * Receives already-normalized fisherfolk rows (the page normalizes the
 * "Master List" .xlsx / template .csv client-side) and upserts them.
 *
 * Request JSON:
 *   {
 *     "data": [ { id_number, full_name, date_of_birth, address, sex, image,
 *                 signature, rsbsa, contact_number, boat_owneroperator,
 *                 capture_fishing, gleaning, vendor, fish_processing,
 *                 aquaculture }, ... ],
 *     "update_existing": true|false   // when true, existing IDs are updated;
 *                                      // otherwise they are skipped.
 *   }
 *
 * Note: image / signature here are the *filenames* declared in the source
 * sheet. The actual image files are uploaded separately (upload-assets.php).
 */

require_once __DIR__ . '/../../config/database-auto.php';
require_once __DIR__ . '/../../config/auth-functions.php';
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

/** Columns we manage, in a stable order. */
const FIELDS = [
    'id_number', 'full_name', 'date_of_birth', 'address', 'sex',
    'image', 'signature', 'rsbsa', 'contact_number',
    'boat_owneroperator', 'capture_fishing', 'gleaning',
    'vendor', 'fish_processing', 'aquaculture',
];
const FLAG_FIELDS = [
    'boat_owneroperator', 'capture_fishing', 'gleaning',
    'vendor', 'fish_processing', 'aquaculture',
];

try {
    $input = file_get_contents('php://input');
    $req = json_decode($input, true);

    if (!isset($req['data']) || !is_array($req['data'])) {
        throw new Exception('Invalid request data');
    }
    $rows = $req['data'];
    $updateExisting = !empty($req['update_existing']);

    $conn = getDBConnection();

    $insertSql = 'INSERT INTO fisherfolk (' . implode(', ', FIELDS) . ') VALUES (' .
        implode(', ', array_map(fn($f) => ':' . $f, FIELDS)) . ')';
    $insertStmt = $conn->prepare($insertSql);

    // Update existing rows. Text/nullable columns use COALESCE so a blank or
    // missing cell in the uploaded sheet NEVER wipes data already on file
    // (e.g. a previously-attached photo filename). Activity flags come from the
    // masterlist's CATEGORY column on every row, so they're set directly.
    $textCols = ['full_name', 'date_of_birth', 'address', 'sex',
        'image', 'signature', 'rsbsa', 'contact_number'];
    $setParts = array_map(fn($f) => "$f = COALESCE(:$f, $f)", $textCols);
    foreach (FLAG_FIELDS as $f) {
        $setParts[] = "$f = :$f";
    }
    $updateSql = 'UPDATE fisherfolk SET ' . implode(', ', $setParts) .
        ' WHERE id_number = :id_number';
    $updateStmt = $conn->prepare($updateSql);

    $checkStmt = $conn->prepare('SELECT COUNT(*) FROM fisherfolk WHERE id_number = ?');

    $inserted = 0; $updated = 0; $skipped = 0; $errors = [];

    $conn->beginTransaction();

    foreach ($rows as $index => $row) {
        $line = $index + 1;
        try {
            $id = trim((string) ($row['id_number'] ?? ''));
            $name = trim((string) ($row['full_name'] ?? ''));
            if ($id === '' || $name === '') {
                $errors[] = "Row $line: missing id_number or full_name";
                $skipped++;
                continue;
            }

            $params = [':id_number' => $id, ':full_name' => $name];
            foreach (['date_of_birth', 'address', 'sex', 'image', 'signature', 'rsbsa', 'contact_number'] as $f) {
                $v = $row[$f] ?? null;
                if (is_string($v)) {
                    $v = trim($v);
                }
                $params[':' . $f] = ($v === '' ? null : $v);
            }
            foreach (FLAG_FIELDS as $f) {
                $params[':' . $f] = !empty($row[$f]) && $row[$f] != '0' ? 1 : 0;
            }

            $checkStmt->execute([$id]);
            $exists = (int) $checkStmt->fetchColumn() > 0;

            if ($exists) {
                if ($updateExisting) {
                    $updateStmt->execute($params);
                    $updated++;
                } else {
                    $skipped++;
                }
            } else {
                $insertStmt->execute($params);
                $inserted++;
            }
        } catch (PDOException $e) {
            $errors[] = "Row $line: " . $e->getMessage();
            $skipped++;
        }
    }

    $conn->commit();

    echo json_encode([
        'success'  => true,
        'inserted' => $inserted,
        'updated'  => $updated,
        'skipped'  => $skipped,
        'total'    => count($rows),
        'errors'   => $errors,
    ]);

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
