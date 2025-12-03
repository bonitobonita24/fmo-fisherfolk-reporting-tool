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
            $imageUrl = '/uploads/faceplaceholder.png';
            if (!empty(trim($imagePath))) {
                $imgPath = trim($imagePath);
                if (!str_contains($imgPath, '/') && !str_starts_with($imgPath, 'http')) {
                    $imageUrl = '/uploads/' . $imgPath;
                } else {
                    $imageUrl = $imgPath;
                }
            }
            
            $signatureUrl = '/uploads/faceplaceholder.png';
            if (!empty(trim($signaturePath))) {
                $sigPath = trim($signaturePath);
                if (!str_contains($sigPath, '/') && !str_starts_with($sigPath, 'http')) {
                    $signatureUrl = '/uploads/' . $sigPath;
                } else {
                    $signatureUrl = $sigPath;
                }
            }
            
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
