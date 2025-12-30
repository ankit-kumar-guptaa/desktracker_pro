<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['employee_id', 'date', 'total_time'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        echo json_encode(['status' => 'error', 'message' => "Missing field: $field"]);
        exit;
    }
}

try {
    $employeeId = $data['employee_id'];
    $date = $data['date'];
    $productiveTime = $data['productive_time'] ?? 0;
    $idleTime = $data['idle_time'] ?? 0;
    $unproductiveTime = $data['unproductive_time'] ?? 0;
    $desktopTime = $data['desktop_time'] ?? 0;
    $totalTime = $data['total_time'] ?? 0;
    
    // Log received data
    error_log("TRACKING UPLOAD: Employee=$employeeId, Date=$date, Productive=$productiveTime, Idle=$idleTime, Total=$totalTime");
    
    // Check if tracking session exists for today
    $stmt = $conn->prepare("SELECT id FROM tracking_sessions WHERE employee_id = ? AND date = ?");
    $stmt->execute([$employeeId, $date]);
    $existingSession = $stmt->fetch();
    
    if ($existingSession) {
        // UPDATE existing session (ADD to existing times)
        $sessionId = $existingSession['id'];
        
        $stmt = $conn->prepare("
            UPDATE tracking_sessions 
            SET productive_time = productive_time + ?,
                idle_time = idle_time + ?,
                unproductive_time = unproductive_time + ?,
                desktop_time = desktop_time + ?,
                total_time = total_time + ?
            WHERE id = ?
        ");
        $stmt->execute([
            $productiveTime,
            $idleTime,
            $unproductiveTime,
            $desktopTime,
            $totalTime,
            $sessionId
        ]);
        
        error_log("UPDATED session $sessionId with Idle=$idleTime, Productive=$productiveTime");
    } else {
        // CREATE new session
        $stmt = $conn->prepare("
            INSERT INTO tracking_sessions 
            (employee_id, date, productive_time, idle_time, unproductive_time, desktop_time, total_time)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $employeeId,
            $date,
            $productiveTime,
            $idleTime,
            $unproductiveTime,
            $desktopTime,
            $totalTime
        ]);
        
        $sessionId = $conn->lastInsertId();
        error_log("CREATED new session $sessionId with Idle=$idleTime, Productive=$productiveTime");
    }
    
    // ==========================================
    // SAVE APPLICATION LOGS
    // ==========================================
    if (!empty($data['applications'])) {
        foreach ($data['applications'] as $app) {
            $stmt = $conn->prepare("
                INSERT INTO application_logs (tracking_session_id, app_name, time_spent)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE time_spent = time_spent + VALUES(time_spent)
            ");
            $stmt->execute([
                $sessionId,
                $app['app'],
                $app['time']
            ]);
        }
        error_log("SAVED " . count($data['applications']) . " application logs");
    }
    
    // ==========================================
    // SAVE URL LOGS
    // ==========================================
    if (!empty($data['urls'])) {
        foreach ($data['urls'] as $url) {
            $stmt = $conn->prepare("
                INSERT INTO url_logs (tracking_session_id, url_title, time_spent, category)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE time_spent = time_spent + VALUES(time_spent)
            ");
            $stmt->execute([
                $sessionId,
                $url['url'],
                $url['time'],
                $url['category']
            ]);
        }
        error_log("SAVED " . count($data['urls']) . " URL logs");
    }
    
    // ==========================================
    // SAVE SCREENSHOTS
    // ==========================================
    if (!empty($data['screenshots'])) {
        $uploadDir = '../assets/uploads/screenshots/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        foreach ($data['screenshots'] as $base64Image) {
            try {
                $imageData = base64_decode($base64Image);
                $filename = 'screenshot_' . $employeeId . '_' . time() . '_' . uniqid() . '.png';
                $filepath = $uploadDir . $filename;
                
                if (file_put_contents($filepath, $imageData)) {
                    $stmt = $conn->prepare("
                        INSERT INTO screenshots (tracking_session_id, image_path, captured_at)
                        VALUES (?, ?, NOW())
                    ");
                    $stmt->execute([
                        $sessionId,
                        'assets/uploads/screenshots/' . $filename
                    ]);
                }
            } catch (Exception $e) {
                error_log("Screenshot save error: " . $e->getMessage());
            }
        }
        error_log("SAVED " . count($data['screenshots']) . " screenshots");
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Tracking data saved successfully',
        'session_id' => $sessionId,
        'saved_data' => [
            'productive_time' => $productiveTime,
            'idle_time' => $idleTime,
            'total_time' => $totalTime,
            'applications' => count($data['applications'] ?? []),
            'urls' => count($data['urls'] ?? []),
            'screenshots' => count($data['screenshots'] ?? [])
        ]
    ]);
    
} catch (Exception $e) {
    error_log("TRACKING UPLOAD ERROR: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
