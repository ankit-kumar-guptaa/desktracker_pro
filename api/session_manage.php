<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $action = $input['action']; // 'logout'
    $session_id = $input['session_id'];
    
    if ($action === 'logout') {
        $stmt = $conn->prepare("UPDATE login_sessions SET logout_time = NOW(), session_duration = TIMESTAMPDIFF(SECOND, login_time, NOW()) WHERE id = ?");
        $stmt->execute([$session_id]);
        echo json_encode(['status' => 'success', 'message' => 'Logout recorded']);
    }
}
?>
