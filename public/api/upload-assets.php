<?php
/**
 * Bulk Photo / Signature Upload API
 *
 * Accepts a batch of image files (multipart form field "files[]") and matches
 * each file by its filename against the fisherfolk.image / fisherfolk.signature
 * columns (the filenames declared in the masterlist). Matching files are saved
 * into public/uploads/ under a hardened single-extension filename, overwriting
 * any existing file (replacement).
 *
 * Files that don't match any record are reported as "unmatched" and are NOT
 * written to disk (so an arbitrary attacker-chosen filename can't be planted).
 *
 * Response: { success, results: [ { file, status, matched_ids[] } ], summary }
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
    if (empty($_FILES['files']) || !is_array($_FILES['files']['name'])) {
        throw new Exception('No files received');
    }

    $conn = getDBConnection();

    // Look up records whose image OR signature matches a given filename
    // (case-insensitive). Returns which column matched so we can save under the
    // exact stored name.
    $findStmt = $conn->prepare(
        'SELECT id_number, image, signature FROM fisherfolk
         WHERE LOWER(image) = LOWER(?) OR LOWER(signature) = LOWER(?)'
    );

    $files = $_FILES['files'];
    $count = count($files['name']);

    $results = [];
    $matched = 0; $unmatched = 0; $failed = 0; $replaced = 0;

    for ($i = 0; $i < $count; $i++) {
        $origName = $files['name'][$i];
        $entry = [
            'error'    => $files['error'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'size'     => $files['size'][$i],
            'name'     => $origName,
        ];

        [$ok, $extOrErr] = validate_uploaded_image($entry);
        if (!$ok) {
            $results[] = ['file' => $origName, 'status' => 'failed', 'reason' => $extOrErr, 'matched_ids' => []];
            $failed++;
            continue;
        }

        $safe = safe_basename($origName);

        // Find matching record(s) by declared filename.
        $findStmt->execute([$safe, $safe]);
        $rows = $findStmt->fetchAll();

        // Only files that a record actually references are written to disk.
        // Unmatched files are reported but NEVER saved — this removes the
        // ability to write an arbitrary attacker-chosen filename to uploads/.
        if (!$rows) {
            $results[] = ['file' => $origName, 'status' => 'unmatched', 'matched_ids' => []];
            $unmatched++;
            continue;
        }

        // Save under the filename the DB stores, but always passed through
        // harden_asset_filename() so the on-disk name has a single validated
        // image extension and no inner dots (no "x.php.jpg" double extension).
        $targetName = null;
        $matchedIds = [];
        foreach ($rows as $r) {
            $matchedIds[] = $r['id_number'];
            if ($targetName === null) {
                if (strcasecmp((string) $r['image'], $safe) === 0 && $r['image'] !== '') {
                    $targetName = $r['image'];
                } elseif (strcasecmp((string) $r['signature'], $safe) === 0 && $r['signature'] !== '') {
                    $targetName = $r['signature'];
                }
            }
        }
        $hardened = harden_asset_filename($targetName ?? $safe);
        if ($hardened === null) {
            $results[] = ['file' => $origName, 'status' => 'failed', 'reason' => 'Unsafe filename', 'matched_ids' => $matchedIds];
            $failed++;
            continue;
        }

        $existed = file_exists(UPLOADS_DIR . '/' . $hardened);
        if (!store_uploaded_asset($entry['tmp_name'], $hardened)) {
            $results[] = ['file' => $origName, 'status' => 'failed', 'reason' => 'Could not save file', 'matched_ids' => []];
            $failed++;
            continue;
        }
        if ($existed) {
            $replaced++;
        }
        $results[] = ['file' => $origName, 'status' => $existed ? 'replaced' : 'matched', 'matched_ids' => $matchedIds];
        $matched++;
    }

    echo json_encode([
        'success' => true,
        'results' => $results,
        'summary' => [
            'total'     => $count,
            'matched'   => $matched,
            'unmatched' => $unmatched,
            'replaced'  => $replaced,
            'failed'    => $failed,
        ],
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
