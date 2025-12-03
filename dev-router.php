<?php
/**
 * Development Server Router
 * Handles routing for PHP built-in server
 * Routes API requests and serves static files
 */

$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Remove query string
$path = strtok($requestPath, '?');

// API routing
if (strpos($path, '/api/') === 0) {
    // Extract API file name
    $apiFile = substr($path, 5); // Remove '/api/' prefix
    $apiFilePath = __DIR__ . '/public/api/' . $apiFile;
    
    if (file_exists($apiFilePath)) {
        require $apiFilePath;
        exit;
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'API endpoint not found',
            'path' => $path
        ]);
        exit;
    }
}

// Serve static files from public directory
if ($path === '/' || $path === '') {
    require __DIR__ . '/public/index.html';
    return true;
}

// Handle assets (CSS, JS, images)
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $path)) {
    $filePath = __DIR__ . '/public' . $path;
    
    if (file_exists($filePath)) {
        // Let PHP's built-in server handle the file
        return false;
    } else {
        http_response_code(404);
        echo "File not found: $path";
        return true;
    }
}

// Default: try to serve from public directory
$publicPath = __DIR__ . '/public' . $path;
if (file_exists($publicPath)) {
    if (is_dir($publicPath)) {
        // Try index.html in directory
        if (file_exists($publicPath . '/index.html')) {
            require $publicPath . '/index.html';
            return true;
        }
    } else {
        // Let PHP handle the file
        return false;
    }
}

// 404 - Not found
http_response_code(404);
echo "<h1>404 - Not Found</h1>";
echo "<p>The requested path '$path' was not found.</p>";
return true;
