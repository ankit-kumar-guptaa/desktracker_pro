<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

include '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        
        // ==========================================
        // GET - Fetch all employees
        // ==========================================
        case 'GET':
            $stmt = $conn->query("SELECT id, emp_code, name, email, status, created_at FROM employees ORDER BY created_at DESC");
            $employees = $stmt->fetchAll();
            
            echo json_encode([
                'status' => 'success',
                'data' => $employees
            ]);
            break;
        
        // ==========================================
        // POST - Create new employee
        // ==========================================
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (empty($data['emp_code']) || empty($data['name']) || empty($data['email']) || empty($data['password'])) {
                echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
                exit;
            }
            
            // Check if employee code already exists
            $stmt = $conn->prepare("SELECT id FROM employees WHERE emp_code = ?");
            $stmt->execute([$data['emp_code']]);
            if ($stmt->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'Employee code already exists']);
                exit;
            }
            
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM employees WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
                exit;
            }
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert employee
            $stmt = $conn->prepare("INSERT INTO employees (emp_code, name, email, password, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->execute([
                $data['emp_code'],
                $data['name'],
                $data['email'],
                $hashedPassword
            ]);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Employee created successfully',
                'id' => $conn->lastInsertId()
            ]);
            break;
        
        // ==========================================
        // PUT - Update employee
        // ==========================================
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                echo json_encode(['status' => 'error', 'message' => 'Employee ID is required']);
                exit;
            }
            
            // Build update query dynamically
            $updates = [];
            $params = [];
            
            if (isset($data['emp_code'])) {
                $updates[] = "emp_code = ?";
                $params[] = $data['emp_code'];
            }
            
            if (isset($data['name'])) {
                $updates[] = "name = ?";
                $params[] = $data['name'];
            }
            
            if (isset($data['email'])) {
                $updates[] = "email = ?";
                $params[] = $data['email'];
            }
            
            if (isset($data['password']) && !empty($data['password'])) {
                $updates[] = "password = ?";
                $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            if (isset($data['status'])) {
                $updates[] = "status = ?";
                $params[] = $data['status'];
            }
            
            if (empty($updates)) {
                echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
                exit;
            }
            
            $params[] = $data['id'];
            
            $sql = "UPDATE employees SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Employee updated successfully'
            ]);
            break;
        
        // ==========================================
        // DELETE - Delete employee
        // ==========================================
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                echo json_encode(['status' => 'error', 'message' => 'Employee ID is required']);
                exit;
            }
            
            $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
            $stmt->execute([$data['id']]);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Employee deleted successfully'
            ]);
            break;
        
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
