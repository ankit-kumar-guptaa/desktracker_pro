<?php
include __DIR__ . '/../config/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS break_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT NOT NULL,
        start_time DATETIME NOT NULL,
        end_time DATETIME NULL,
        duration INT DEFAULT 0,
        date DATE NOT NULL,
        reason VARCHAR(255) DEFAULT 'Break',
        FOREIGN KEY (employee_id) REFERENCES employees(id)
    )";
    
    $conn->exec($sql);
    echo "Table 'break_logs' created successfully (or already exists).";
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>