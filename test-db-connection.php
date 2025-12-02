<?php
/**
 * Database Connection Test Script
 * Use this to verify database connection in production
 */

// Load environment configuration
require_once __DIR__ . '/config/env.php';

echo "Environment: " . APP_ENV . "\n";
echo "Testing database connection...\n\n";

try {
    $conn = getDBConnection();
    echo "✅ Database connection successful!\n";
    echo "Connected to: " . DB_NAME . "\n";
    echo "Host: " . DB_HOST . "\n";
    
    // Test query
    $stmt = $conn->query("SELECT COUNT(*) as count FROM fisherfolk");
    $result = $stmt->fetch();
    echo "Total fisherfolk records: " . $result['count'] . "\n";
    
    echo "\n✨ Connection test passed!\n";
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
