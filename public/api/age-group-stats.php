<?php
/**
 * Age Group Statistics API
 * Returns count of fisherfolk by age group
 */

require_once __DIR__ . '/../../config/database-auto.php';
require_once __DIR__ . '/../../config/auth-functions.php';
require_api_auth();

setJSONHeaders();

try {
    // SQLite age in years: year diff minus 1 if this year's birthday hasn't occurred yet
    $age = "((strftime('%Y','now') - strftime('%Y', date_of_birth))
             - (strftime('%m-%d','now') < strftime('%m-%d', date_of_birth)))";

    $sql = "SELECT
                CASE
                    WHEN $age < 25 THEN 'Under 25'
                    WHEN $age BETWEEN 25 AND 34 THEN '25-34'
                    WHEN $age BETWEEN 35 AND 44 THEN '35-44'
                    WHEN $age BETWEEN 45 AND 54 THEN '45-54'
                    WHEN $age BETWEEN 55 AND 64 THEN '55-64'
                    ELSE '65 and above'
                END as age_group,
                COUNT(*) as count
            FROM fisherfolk
            WHERE date_of_birth IS NOT NULL AND date_of_birth != ''
            GROUP BY age_group
            ORDER BY 
                CASE age_group
                    WHEN 'Under 25' THEN 1
                    WHEN '25-34' THEN 2
                    WHEN '35-44' THEN 3
                    WHEN '45-54' THEN 4
                    WHEN '55-64' THEN 5
                    WHEN '65 and above' THEN 6
                END";
    
    $results = executeQuery($sql);
    
    echo json_encode([
        'success' => true,
        'data' => $results
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
