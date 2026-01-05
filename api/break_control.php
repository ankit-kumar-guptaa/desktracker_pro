<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include '../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);

$action = $data['action'] ?? '';
$employee_id = $data['employee_id'] ?? null;
$date = $data['date'] ?? date('Y-m-d');
$break_id = $data['break_id'] ?? null;

// Ensure table exists (Fallback)
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS break_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT NOT NULL,
        start_time DATETIME NOT NULL,
        end_time DATETIME NULL,
        duration INT DEFAULT 0,
        date DATE NOT NULL,
        reason VARCHAR(255) DEFAULT 'Break',
        FOREIGN KEY (employee_id) REFERENCES employees(id)
    )");
} catch (Exception $e) {
    // Ignore if table exists or error
}

try {
    if ($action === 'start') {
        $stmt = $conn->prepare("INSERT INTO break_logs (employee_id, start_time, date) VALUES (?, NOW(), ?)");
        $stmt->execute([$employee_id, $date]);
        
        echo json_encode([
            'status' => 'success',
            'break_id' => $conn->lastInsertId(),
            'message' => 'Break started'
        ]);
    } 
    elseif ($action === 'end') {
        if ($break_id) {
            // Calculate duration
            $stmt = $conn->prepare("SELECT start_time FROM break_logs WHERE id = ?");
            $stmt->execute([$break_id]);
            $row = $stmt->fetch();
            
            if ($row) {
                $start_time = strtotime($row['start_time']);
                $end_time = time();
                $duration = $end_time - $start_time;
                
                $stmt = $conn->prepare("UPDATE break_logs SET end_time = NOW(), duration = ? WHERE id = ?");
                $stmt->execute([$duration, $break_id]);
                
                echo json_encode([
                    'status' => 'success',
                    'duration' => $duration,
                    'message' => 'Break ended'
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Break not found']);
            }
        } else {
            // If no break_id provided, find the last open break for this employee today
            $stmt = $conn->prepare("SELECT id, start_time FROM break_logs WHERE employee_id = ? AND date = ? AND end_time IS NULL ORDER BY id DESC LIMIT 1");
            $stmt->execute([$employee_id, $date]);
            $row = $stmt->fetch();
            
            if ($row) {
                $start_time = strtotime($row['start_time']);
                $end_time = time();
                $duration = $end_time - $start_time;
                
                $stmt = $conn->prepare("UPDATE break_logs SET end_time = NOW(), duration = ? WHERE id = ?");
                $stmt->execute([$duration, $row['id']]);
                
                echo json_encode([
                    'status' => 'success',
                    'duration' => $duration,
                    'message' => 'Break ended (Auto-detected)'
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No active break found']);
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>