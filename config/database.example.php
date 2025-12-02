<?php
/**
 * Database Configuration Example
 * Copy this file to database.php and update with your credentials
 */

// Database credentials
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'fisherfolk_db');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get database connection
 * @return PDO Database connection object
 */
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode([
                'error' => 'Database connection failed',
                'message' => $e->getMessage()
            ]));
        }
    }
    
    return $conn;
}

/**
 * Execute a query and return results
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array Query results
 */
function executeQuery($sql, $params = []) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        http_response_code(500);
        die(json_encode([
            'error' => 'Query execution failed',
            'message' => $e->getMessage()
        ]));
    }
}

/**
 * Set JSON response headers
 */
function setJSONHeaders() {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}
