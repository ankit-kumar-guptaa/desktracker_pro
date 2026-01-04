<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include '../config/db.php';

$employeeId = $_GET['employee_id'] ?? null;

if (!$employeeId) {
    echo json_encode(['status' => 'error', 'message' => 'Employee ID required']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT image_data, last_updated 
        FROM live_screens 
        WHERE employee_id = ? 
    ");
    $stmt->execute([$employeeId]);
    $screen = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($screen) {
        // Calculate time ago
        $diff = time() - strtotime($screen['last_updated']);
        $timeAgo = ($diff < 60) ? $diff . 's ago' : floor($diff / 60) . 'm ago';
        $isLive = ($diff < 30); // Considered live if < 30s old
        
        echo json_encode([
            'status' => 'success', 
            'image' => $screen['image_data'],
            'last_updated' => $screen['last_updated'],
            'time_ago' => $timeAgo,
            'is_live' => $isLive
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No screen data found']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
