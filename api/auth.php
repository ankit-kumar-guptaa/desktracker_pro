<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$type = $data['type'] ?? 'employee'; // 'admin' or 'employee'

if (empty($username) || empty($password)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Username and password are required'
    ]);
    exit;
}

try {
    if ($type === 'admin') {
        // ==========================================
        // ADMIN LOGIN
        // ==========================================
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            session_start();
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Admin login successful',
                'data' => [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'email' => $admin['email']
                ]
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid admin credentials'
            ]);
        }
        
    } else {
        // ==========================================
        // EMPLOYEE LOGIN
        // ==========================================
        $stmt = $conn->prepare("SELECT * FROM employees WHERE emp_code = ?");
        $stmt->execute([$username]);
        $employee = $stmt->fetch();
        
        // Check if employee exists
        if (!$employee) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Employee not found'
            ]);
            exit;
        }
        
        // Check if employee is active
        if ($employee['status'] !== 'active') {
            echo json_encode([
                'status' => 'error',
                'message' => 'Your account is inactive. Please contact administrator.'
            ]);
            exit;
        }
        
        // Verify password
        $passwordMatch = false;
        
        // Try password_verify first (for hashed passwords)
        if (password_verify($password, $employee['password'])) {
            $passwordMatch = true;
        } 
        // Fallback: Direct comparison (for plain text passwords - backward compatibility)
        elseif ($employee['password'] === $password) {
            $passwordMatch = true;
            
            // Update to hashed password for security
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE employees SET password = ? WHERE id = ?");
            $updateStmt->execute([$hashedPassword, $employee['id']]);
        }
        
        if ($passwordMatch) {
            // Create login session
            $stmt = $conn->prepare("INSERT INTO login_sessions (employee_id, login_time) VALUES (?, NOW())");
            $stmt->execute([$employee['id']]);
            $sessionId = $conn->lastInsertId();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful',
                'session_id' => $sessionId,
                'data' => [
                    'id' => $employee['id'],
                    'emp_code' => $employee['emp_code'],
                    'name' => $employee['name'],
                    'email' => $employee['email']
                ]
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid password'
            ]);
        }
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
