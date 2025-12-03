<!DOCTYPE html>
<html>
<head>
    <title>Image Path Debugger</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f28500; color: white; }
        img { max-width: 100px; max-height: 100px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Image Path Debugger</h1>
    
    <?php
    require_once __DIR__ . '/../config/database-auto.php';

    $scriptDir = '/';
    if (isset($_SERVER['SCRIPT_NAME'])) {
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $scriptDir = ($dir === '' || $dir === '.') ? '/' : rtrim($dir, '/') . '/';
    }

    $media_base_path = $scriptDir;
    $placeholder_image = $media_base_path . 'uploads/faceplaceholder.png';

    function resolve_media_path(?string $rawPath, ?string $placeholder = null): string {
        global $media_base_path, $placeholder_image;
        if ($placeholder === null) {
            $placeholder = $placeholder_image;
        }
        if ($rawPath === null) {
            return $placeholder;
        }

        $path = trim($rawPath);
        if ($path === '') {
            return $placeholder;
        }

        if (preg_match('#^https?://#i', $path)) {
            try {
                $parts = parse_url($path);
                $pathname = $parts['path'] ?? '';
                $uploadsPos = stripos($pathname, '/uploads/');
                if ($uploadsPos !== false) {
                    $normalized = substr($pathname, $uploadsPos + 1); // drop leading slash
                    return $media_base_path . $normalized;
                }

                $host = $parts['host'] ?? '';
                $currentHost = $_SERVER['HTTP_HOST'] ?? '';
                if ($host === $currentHost) {
                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                    $rest = $host
                        . (isset($parts['port']) ? ':' . $parts['port'] : '')
                        . ($pathname ?? '')
                        . (isset($parts['query']) ? '?' . $parts['query'] : '')
                        . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
                    return $scheme . $rest;
                }
            } catch (Exception $e) {
                // ignore and fall through
            }
            if (str_starts_with($path, 'http://') && (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) {
                try {
                    $url = parse_url($path);
                    $host = $_SERVER['HTTP_HOST'] ?? '';
                    if (!empty($url['host']) && $url['host'] === $host) {
                        $scheme = 'https://';
                        $rest = ($url['host'] ?? '')
                            . (($url['port'] ?? '') ? ':' . $url['port'] : '')
                            . ($url['path'] ?? '')
                            . (isset($url['query']) ? '?' . $url['query'] : '')
                            . (isset($url['fragment']) ? '#' . $url['fragment'] : '');
                        return $scheme . $rest;
                    }
                } catch (Exception $e) {
                    // ignore and fall through
                }
            }
            return $path;
        }

        if (preg_match('#^(data:|blob:|//)#i', $path)) {
            return $path;
        }

        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#^\./+#', '', $path);
        while (str_starts_with($path, '../')) {
            $path = substr($path, 3);
        }

        $lower = strtolower($path);
        $uploadsPos = strpos($lower, '/uploads/');
        if ($uploadsPos !== false) {
            $path = substr($path, $uploadsPos);
        } else {
            $uploadsPos = strpos($lower, 'uploads/');
            if ($uploadsPos !== false) {
                $path = substr($path, $uploadsPos);
                if ($path !== '' && $path[0] !== '/') {
                    $path = '/' . $path;
                }
            }
        }

        if (str_starts_with($path, '/uploads/')) {
            return $media_base_path . ltrim($path, '/');
        }

        if (str_starts_with($path, 'uploads/')) {
            return $media_base_path . $path;
        }

        if (!str_contains($path, '/')) {
            return $media_base_path . 'uploads/' . $path;
        }

        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        return $media_base_path . ltrim($path, '/');
    }
    
    try {
        $conn = getDBConnection();
        $stmt = $conn->query("SELECT id_number, full_name, image, signature FROM fisherfolk LIMIT 10");
        $records = $stmt->fetchAll();
        
        echo "<table>";
        echo "<tr>
                <th>ID</th>
                <th>Name</th>
                <th>DB Image Value</th>
                <th>Constructed Path</th>
                <th>Preview</th>
                <th>DB Signature Value</th>
                <th>Constructed Path</th>
                <th>Preview</th>
              </tr>";
        
        foreach ($records as $row) {
            $imagePath = $row['image'] ?? '';
            $signaturePath = $row['signature'] ?? '';
            
            // Construct the paths like the JavaScript does
            $imageUrl = resolve_media_path($imagePath);
            $signatureUrl = resolve_media_path($signaturePath);
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($imagePath) . "</td>";
            echo "<td>" . htmlspecialchars($imageUrl) . "</td>";
            echo "<td><img src='" . htmlspecialchars($imageUrl) . "' onerror=\"this.parentElement.innerHTML='<span class=error>Not Found</span>'\"></td>";
            echo "<td>" . htmlspecialchars($signaturePath) . "</td>";
            echo "<td>" . htmlspecialchars($signatureUrl) . "</td>";
            echo "<td><img src='" . htmlspecialchars($signatureUrl) . "' onerror=\"this.parentElement.innerHTML='<span class=error>Not Found</span>'\"></td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
    } catch (Exception $e) {
        echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
</body>
</html>
