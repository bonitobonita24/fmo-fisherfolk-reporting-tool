<?php
/**
 * Shared helpers for photo / signature uploads.
 * Fisherfolk Management System - Calapan City FMO
 *
 * Assets live in public/uploads/ and the DB stores the bare filename in the
 * fisherfolk.image / fisherfolk.signature columns. The dashboard JS resolves
 * a stored filename to /uploads/<filename>.
 */

define('UPLOADS_DIR', __DIR__ . '/../public/uploads');

// Extensions we accept for a fisherfolk photo or signature.
const ALLOWED_IMAGE_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// Max size per uploaded image (10 MB).
const MAX_IMAGE_BYTES = 10 * 1024 * 1024;

/**
 * Strip any directory component and collapse anything unsafe so a filename can
 * never escape the uploads directory. Preserves the original case (the DB may
 * store MixedCase / UPPERCASE extensions).
 */
function safe_basename($name) {
    $name = basename((string) $name);              // drop path traversal
    $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name); // whitelist chars
    $name = ltrim($name, '.');                     // no leading dots
    return $name;
}

/**
 * Build a canonical "<id>.<ext>" filename for a record's asset.
 * Dots are stripped from the id stem so the ONLY dot is the one before the
 * extension (prevents "id.php" -> "id.php.jpg" multi-extension names).
 */
function asset_filename_for_id($idNumber, $ext) {
    $safeId = preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $idNumber);
    $safeId = trim($safeId, '._-');
    if ($safeId === '') {
        $safeId = 'asset';
    }
    $ext = strtolower(preg_replace('/[^A-Za-z0-9]/', '', (string) $ext));
    if (!in_array($ext, ALLOWED_IMAGE_EXT, true)) {
        $ext = 'jpg';
    }
    return $safeId . '.' . $ext;
}

/**
 * Produce a safe on-disk filename for a declared asset name: exactly one
 * extension (a validated image type) and no inner dots, so a crafted name like
 * "shell.php.jpg" can never be written as an executable double-extension file.
 * Returns null when the name has no acceptable image extension.
 */
function harden_asset_filename($name) {
    $name = basename((string) $name);
    $origExt = pathinfo($name, PATHINFO_EXTENSION);  // keep original case (DB stores .JPG/.PNG)
    $check = strtolower($origExt);
    if ($check === 'jpe') {
        $check = 'jpeg';
        $origExt = 'jpeg';
    }
    if (!in_array($check, ALLOWED_IMAGE_EXT, true)) {
        return null;
    }
    $stem = pathinfo($name, PATHINFO_FILENAME);     // everything before the last dot
    $stem = str_replace('.', '_', $stem);            // neutralize inner ".php" etc.
    $stem = preg_replace('/[^A-Za-z0-9_-]/', '_', $stem);
    $stem = trim($stem, '._-');
    if ($stem === '') {
        $stem = 'asset';
    }
    return $stem . '.' . $origExt;
}

/**
 * Validate a single $_FILES[...] entry. Returns [ok(bool), ext(string)|error(string)].
 */
function validate_uploaded_image($file) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return [false, 'Invalid upload'];
    }
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return [false, 'File too large'];
        case UPLOAD_ERR_NO_FILE:
            return [false, 'No file uploaded'];
        default:
            return [false, 'Upload error (code ' . $file['error'] . ')'];
    }

    if ($file['size'] > MAX_IMAGE_BYTES) {
        return [false, 'File exceeds 10 MB limit'];
    }

    // Verify it is actually an image (defeats renamed non-images).
    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        return [false, 'Not a valid image file'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext === 'jpe') {
        $ext = 'jpeg';
    }
    if (!in_array($ext, ALLOWED_IMAGE_EXT, true)) {
        return [false, 'Unsupported file type (.' . $ext . ')'];
    }

    return [true, $ext];
}

/**
 * Move an uploaded temp file into the uploads directory under $targetName,
 * overwriting any existing file. Returns true on success.
 */
function store_uploaded_asset($tmpName, $targetName) {
    if (!is_dir(UPLOADS_DIR)) {
        @mkdir(UPLOADS_DIR, 0775, true);
    }
    $dest = UPLOADS_DIR . '/' . $targetName;
    if (file_exists($dest)) {
        @unlink($dest); // replace / override existing asset
    }
    if (!move_uploaded_file($tmpName, $dest)) {
        // Fall back to copy for non-HTTP-upload contexts (should not happen in prod).
        if (!@copy($tmpName, $dest)) {
            return false;
        }
    }
    @chmod($dest, 0644);
    return true;
}
