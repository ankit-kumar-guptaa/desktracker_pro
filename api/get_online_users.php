<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include '../config/db.php';

try {
    // Get all employees and their last active status from live_screens
    // We want all employees, with a flag if they are online
    
    // First ensure live_screens table exists to avoid errors
    $checkTable = $conn->query("SHOW TABLES LIKE 'live_screens'");
    if ($checkTable->rowCount() == 0) {
        // Table doesn't exist, so no one is online. Return all employees as offline.
        $stmt = $conn->query("SELECT id, name, emp_code FROM employees ORDER BY name ASC");
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($employees as &$emp) {
            $emp['is_online'] = false;
            $emp['last_seen'] = 'Never';
        }
        echo json_encode(['status' => 'success', 'data' => $employees]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT 
            e.id, 
            e.name, 
            e.emp_code, 
            ls.last_updated
        FROM employees e
        LEFT JOIN live_screens ls ON e.id = ls.employee_id
        ORDER BY 
            CASE WHEN ls.last_updated > (NOW() - INTERVAL 2 MINUTE) THEN 0 ELSE 1 END,
            e.name ASC
    ");
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($employees as &$emp) {
        $isOnline = false;
        $lastSeen = 'Never';
        
        if ($emp['last_updated']) {
            $timeDiff = time() - strtotime($emp['last_updated']);
            if ($timeDiff < 60) { // Considered online if updated in last 60 seconds
                $isOnline = true;
                $lastSeen = 'Just now';
            } elseif ($timeDiff < 3600) {
                $lastSeen = floor($timeDiff / 60) . 'm ago';
            } else {
                $lastSeen = date('h:i A', strtotime($emp['last_updated']));
            }
        }
        
        $emp['is_online'] = $isOnline;
        $emp['last_seen_text'] = $lastSeen;
    }
    
    echo json_encode(['status' => 'success', 'data' => $employees]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
