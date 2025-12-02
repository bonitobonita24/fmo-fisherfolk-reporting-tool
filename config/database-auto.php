<?php
/**
 * Auto-detecting Database Configuration
 * Fisherfolk Management System - Calapan City FMO
 * Automatically loads production or development config
 */

// Detect if we're on localhost (development)
$isLocalhost = (
    strpos(gethostname(), 'localhost') !== false || 
    strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
    strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false ||
    (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development')
);

if ($isLocalhost) {
    // DEVELOPMENT CONFIGURATION
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_USER', 'root');
    define('DB_PASS', '4,q@TG^Gy.HzM%ZL-B');
    define('DB_NAME', 'fmo_fisherfolk_management_system');
    define('DB_CHARSET', 'utf8mb4');
} else {
    // PRODUCTION CONFIGURATION
    define('DB_HOST', 's1105.usc1.mysecurecloudhost.com');
    define('DB_PORT', '3306');
    define('DB_USER', 'jerlanlo_fisherfolks');
    define('DB_PASS', '!kx^|MU6ASjP#HdN8');
    define('DB_NAME', 'jerlanlo_powerbyteitsolutions_com_fisherfolks');
    define('DB_CHARSET', 'utf8mb4');
}

/**
 * Get database connection
 * @return PDO Database connection object
 */
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
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
