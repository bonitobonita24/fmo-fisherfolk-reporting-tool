<?php
/**
 * Test if image files are accessible
 */

header('Content-Type: application/json');

$testImages = [
    '000704-2015.JPG',
    '02-17-520-500-04319.JPG',
    '02-17-5205000-04268.JPG'
];

$results = [];

foreach ($testImages as $img) {
    $path = __DIR__ . '/../images/' . $img;
    $webPath = '/images/' . $img;
    
    $results[] = [
        'filename' => $img,
        'server_path' => $path,
        'web_path' => $webPath,
        'file_exists' => file_exists($path),
        'is_readable' => file_exists($path) ? is_readable($path) : false,
        'file_size' => file_exists($path) ? filesize($path) : 0
    ];
}

// Check if images directory exists
$imagesDir = __DIR__ . '/../images';
$results['directory_check'] = [
    'path' => $imagesDir,
    'exists' => file_exists($imagesDir),
    'is_directory' => is_dir($imagesDir),
    'is_writable' => is_writable($imagesDir)
];

echo json_encode($results, JSON_PRETTY_PRINT);
