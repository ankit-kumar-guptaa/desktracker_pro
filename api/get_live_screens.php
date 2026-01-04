<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include '../config/db.php';

try {
    // Check if table exists first (graceful degradation)
    $checkTable = $conn->query("SHOW TABLES LIKE 'live_screens'");
    if ($checkTable->rowCount() == 0) {
        echo json_encode(['status' => 'success', 'data' => []]);
        exit;
    }

    // Get screens updated in last 5 minutes (allowing for some delay)
    $stmt = $conn->prepare("
        SELECT ls.employee_id, ls.image_data, ls.last_updated, e.name, e.emp_code
        FROM live_screens ls
        JOIN employees e ON ls.employee_id = e.id
        WHERE ls.last_updated > (NOW() - INTERVAL 5 MINUTE)
        ORDER BY ls.last_updated DESC
    ");
    $stmt->execute();
    $screens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process data if needed (e.g. format time)
    foreach ($screens as &$screen) {
        // Calculate time ago
        $lastUpdated = strtotime($screen['last_updated']);
        $now = time();
        $diff = $now - $lastUpdated;
        
        if ($diff < 60) {
            $screen['time_ago'] = $diff . 's ago';
        } else {
            $screen['time_ago'] = floor($diff / 60) . 'm ago';
        }
        
        // Status color based on recency
        $screen['status_color'] = ($diff < 30) ? 'success' : 'warning';
    }
    
    echo json_encode(['status' => 'success', 'data' => $screens]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
