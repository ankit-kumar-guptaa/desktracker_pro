<?php
include '../config/db.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password)) {
        $message = 'All fields are required';
        $messageType = 'danger';
    } else {
        try {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetch()) {
                $message = 'Username already exists';
                $messageType = 'warning';
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new admin
                $stmt = $conn->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashedPassword]);
                
                $message = 'Admin account created successfully! You can now <a href="login.php">Login</a>';
                $messageType = 'success';
            }
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin - DeskTracker Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 450px;
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header h2 {
            color: #667eea;
            font-weight: 700;
        }
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .alert-success a {
            color: #0f5132;
            font-weight: 700;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header">
            <h2>🛠️ Create Admin</h2>
            <p class="text-muted">Register New Administrator</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-register w-100">Create Admin Account</button>
            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none">Back to Login</a>
            </div>
        </form>
        
        <div class="alert alert-warning mt-4 mb-0 py-2" style="font-size: 13px;">
            <strong>⚠️ Security Warning:</strong> Please delete this file (<code>create_admin.php</code>) after creating your account to prevent unauthorized access.
        </div>
    </div>
</body>
</html>