<?php
/**
 * Authentication helpers for the Fisherfolk dashboard.
 * Session-based, single-user. Credentials come from config/auth.php
 * (gitignored) or AUTH_USERNAME / AUTH_PASSWORD_HASH environment variables.
 */

function auth_boot() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
        session_start();
    }
}

function auth_config() {
    $file = __DIR__ . '/auth.php';
    if (is_file($file)) {
        return require $file;
    }
    // Fallback to environment only (e.g. published image without the file)
    $hash = getenv('AUTH_PASSWORD_HASH');
    if ($hash) {
        return ['username' => getenv('AUTH_USERNAME') ?: 'admin', 'password_hash' => $hash];
    }
    return null;
}

function auth_is_logged_in() {
    auth_boot();
    return !empty($_SESSION['authenticated']);
}

/**
 * Validate a username/password pair. Returns true on success and establishes
 * the session.
 */
function auth_attempt($username, $password) {
    $cfg = auth_config();
    if (!$cfg || empty($cfg['password_hash'])) {
        return false;
    }
    $userOk = hash_equals((string) $cfg['username'], (string) $username);
    $passOk = password_verify((string) $password, $cfg['password_hash']);
    if ($userOk && $passOk) {
        auth_boot();
        session_regenerate_id(true);
        $_SESSION['authenticated'] = true;
        $_SESSION['username'] = $cfg['username'];
        return true;
    }
    return false;
}

function auth_logout() {
    auth_boot();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** Guard for HTML pages — redirect to the login page when not authenticated. */
function require_page_auth() {
    if (!auth_is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/** Guard for JSON APIs — respond 401 when not authenticated. */
function require_api_auth() {
    if (!auth_is_logged_in()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Authentication required', 'auth' => false]);
        exit;
    }
}
