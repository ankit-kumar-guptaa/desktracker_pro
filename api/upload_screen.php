<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include '../config/db.php';

// Check if table exists, if not create it
try {
    $checkTable = $conn->query("SHOW TABLES LIKE 'live_screens'");
    if ($checkTable->rowCount() == 0) {
        $createTable = "CREATE TABLE live_screens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL UNIQUE,
            image_data LONGBLOB,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        )";
        $conn->exec($createTable);
    }
} catch (PDOException $e) {
    // If error in table creation (maybe foreign key issue if employees table doesn't exist yet?), try simple table
    try {
        $createTable = "CREATE TABLE IF NOT EXISTS live_screens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL UNIQUE,
            image_data LONGBLOB,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->exec($createTable);
    } catch (PDOException $e2) {
        echo json_encode(['status' => 'error', 'message' => 'Table creation failed: ' . $e2->getMessage()]);
        exit;
    }
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['employee_id']) || !isset($data['image'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$employeeId = $data['employee_id'];
$imageData = $data['image']; // Base64 string

try {
    // Upsert the screen data
    $stmt = $conn->prepare("
        INSERT INTO live_screens (employee_id, image_data, last_updated) 
        VALUES (?, ?, NOW()) 
        ON DUPLICATE KEY UPDATE image_data = VALUES(image_data), last_updated = NOW()
    ");
    $stmt->execute([$employeeId, $imageData]);

    echo json_encode(['status' => 'success', 'message' => 'Screen updated']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
