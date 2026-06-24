<?php
/**
 * Database Configuration (SQLite)
 * Fisherfolk Management System - Calapan City FMO
 *
 * The app uses a single-file SQLite database (data/fisherfolk.sqlite).
 * No server, no credentials. The file is created automatically from
 * sql/schema.sqlite.sql on first connection if it does not yet exist.
 */

define('DB_PATH', __DIR__ . '/../data/fisherfolk.sqlite');
define('DB_SCHEMA', __DIR__ . '/../sql/schema.sqlite.sql');

/**
 * Get database connection
 * @return PDO Database connection object
 */
function getDBConnection() {
    static $conn = null;

    if ($conn === null) {
        try {
            // Ensure the data directory exists
            $dir = dirname(DB_PATH);
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }

            $needsBootstrap = !file_exists(DB_PATH) || filesize(DB_PATH) === 0;

            $dsn = 'sqlite:' . DB_PATH;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            $conn = new PDO($dsn, null, null, $options);
            $conn->exec('PRAGMA foreign_keys = ON');

            // Create schema if the fisherfolk table is missing
            if ($needsBootstrap || !tableExists($conn, 'fisherfolk')) {
                bootstrapSchema($conn);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode([
                'error'   => 'Database connection failed',
                'message' => $e->getMessage()
            ]));
        }
    }

    return $conn;
}

/**
 * Check whether a table exists in the SQLite database
 */
function tableExists(PDO $conn, $name) {
    $stmt = $conn->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name = ?");
    $stmt->execute([$name]);
    return (bool) $stmt->fetchColumn();
}

/**
 * Load and execute the schema file (idempotent: uses IF NOT EXISTS)
 */
function bootstrapSchema(PDO $conn) {
    if (!file_exists(DB_SCHEMA)) {
        return;
    }
    $sql = file_get_contents(DB_SCHEMA);
    // PDO's sqlite driver executes the whole script, triggers included.
    $conn->exec($sql);
}

/**
 * Execute a query and return results
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array Query results
 */
function executeQuery($sql, $params = []) {
    $conn = getDBConnection();
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Set JSON headers
 */
function setJSONHeaders() {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}
