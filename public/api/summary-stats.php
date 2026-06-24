<?php
/**
 * Summary Statistics API
 * Returns overall summary statistics
 */

require_once __DIR__ . '/../../config/database-auto.php';
require_once __DIR__ . '/../../config/auth-functions.php';
require_api_auth();

setJSONHeaders();

try {
    // Total fisherfolk
    $totalSql = "SELECT COUNT(*) as total FROM fisherfolk";
    $totalResult = executeQuery($totalSql);
    $total = $totalResult[0]['total'];
    
    // Male count
    $maleSql = "SELECT COUNT(*) as count FROM fisherfolk WHERE sex = 'Male'";
    $maleResult = executeQuery($maleSql);
    $male = $maleResult[0]['count'];
    
    // Female count
    $femaleSql = "SELECT COUNT(*) as count FROM fisherfolk WHERE sex = 'Female'";
    $femaleResult = executeQuery($femaleSql);
    $female = $femaleResult[0]['count'];
    
    // Barangays count
    $barangaySql = "SELECT COUNT(DISTINCT address) as count FROM fisherfolk";
    $barangayResult = executeQuery($barangaySql);
    $barangays = $barangayResult[0]['count'];
    
    // Recently registered (last 30 days)
    $recentSql = "SELECT COUNT(*) as count FROM fisherfolk WHERE date_registered >= datetime('now', '-30 days')";
    $recentResult = executeQuery($recentSql);
    $recent = $recentResult[0]['count'];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_fisherfolk' => $total,
            'male' => $male,
            'female' => $female,
            'barangays' => $barangays,
            'recent_registrations' => $recent
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
