<?php
// ==========================================
// DATABASE HELPER FUNCTIONS
// ==========================================

// Get all employees
function getAllEmployees($conn) {
    $stmt = $conn->query("SELECT * FROM employees WHERE status = 'active' ORDER BY name ASC");
    return $stmt->fetchAll();
}

// Get employee by ID
function getEmployeeById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Get employee by code
function getEmployeeByCode($conn, $empCode) {
    $stmt = $conn->prepare("SELECT * FROM employees WHERE emp_code = ?");
    $stmt->execute([$empCode]);
    return $stmt->fetch();
}

// ==========================================
// TRACKING DATA FUNCTIONS
// ==========================================

// Get tracking data for date range
function getTrackingData($conn, $employeeId, $startDate, $endDate) {
    $stmt = $conn->prepare("
        SELECT * FROM tracking_sessions 
        WHERE employee_id = ? AND date BETWEEN ? AND ?
        ORDER BY date DESC
    ");
    $stmt->execute([$employeeId, $startDate, $endDate]);
    return $stmt->fetchAll();
}

// Get today's tracking summary
function getTodaysSummary($conn, $date = null) {
    if (!$date) $date = date('Y-m-d');
    
    $stmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT employee_id) as active_employees,
            SUM(total_time) as total_time,
            SUM(productive_time) as productive_time,
            SUM(idle_time) as idle_time,
            ROUND((SUM(productive_time) / SUM(total_time)) * 100, 2) as productivity_percent
        FROM tracking_sessions
        WHERE date = ?
    ");
    $stmt->execute([$date]);
    return $stmt->fetch();
}

// ==========================================
// TIME FORMATTING FUNCTIONS
// ==========================================

// Convert seconds to HH:MM:SS format
function formatSeconds($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    return sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
}

// Convert seconds to readable format
function formatTimeReadable($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    
    if ($hours > 0) {
        return $hours . 'h ' . $minutes . 'm';
    }
    return $minutes . 'm';
}

// Convert seconds to decimal hours
function secondsToHours($seconds) {
    return round($seconds / 3600, 2);
}

// ==========================================
// AUTHENTICATION FUNCTIONS
// ==========================================

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Require admin login
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Get logged in admin info
function getAdminInfo($conn) {
    if (!isAdminLoggedIn()) return null;
    
    $stmt = $conn->prepare("SELECT id, username, email FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

// ==========================================
// DATE HELPER FUNCTIONS
// ==========================================

// Get date range for reports
function getDateRange($period = 'today') {
    $endDate = date('Y-m-d');
    
    switch ($period) {
        case 'today':
            $startDate = $endDate;
            break;
        case 'yesterday':
            $startDate = date('Y-m-d', strtotime('-1 day'));
            $endDate = $startDate;
            break;
        case 'week':
            $startDate = date('Y-m-d', strtotime('-7 days'));
            break;
        case 'month':
            $startDate = date('Y-m-d', strtotime('-30 days'));
            break;
        default:
            $startDate = $endDate;
    }
    
    return ['start' => $startDate, 'end' => $endDate];
}

// Get week dates (Monday to Sunday)
function getWeekDates($date = null) {
    if (!$date) $date = date('Y-m-d');
    
    $timestamp = strtotime($date);
    $dayOfWeek = date('N', $timestamp); // 1 (Monday) to 7 (Sunday)
    
    $mondayTimestamp = strtotime("-" . ($dayOfWeek - 1) . " days", $timestamp);
    $sundayTimestamp = strtotime("+" . (7 - $dayOfWeek) . " days", $timestamp);
    
    return [
        'start' => date('Y-m-d', $mondayTimestamp),
        'end' => date('Y-m-d', $sundayTimestamp)
    ];
}

// ==========================================
// SCREENSHOT FUNCTIONS
// ==========================================

// Get screenshots for employee
function getScreenshots($conn, $employeeId, $date, $limit = 20) {
    $stmt = $conn->prepare("
        SELECT s.* 
        FROM screenshots s
        JOIN tracking_sessions t ON s.tracking_session_id = t.id
        WHERE t.employee_id = ? AND t.date = ?
        ORDER BY s.captured_at DESC
        LIMIT ?
    ");
    $stmt->execute([$employeeId, $date, $limit]);
    return $stmt->fetchAll();
}

// Delete old screenshots (older than X days)
function deleteOldScreenshots($conn, $days = 30) {
    $cutoffDate = date('Y-m-d', strtotime("-$days days"));
    
    // Get screenshot files to delete
    $stmt = $conn->prepare("
        SELECT s.image_path 
        FROM screenshots s
        JOIN tracking_sessions t ON s.tracking_session_id = t.id
        WHERE t.date < ?
    ");
    $stmt->execute([$cutoffDate]);
    $screenshots = $stmt->fetchAll();
    
    // Delete files from disk
    foreach ($screenshots as $screenshot) {
        $filePath = __DIR__ . '/../' . $screenshot['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    // Delete database records
    $stmt = $conn->prepare("
        DELETE s FROM screenshots s
        JOIN tracking_sessions t ON s.tracking_session_id = t.id
        WHERE t.date < ?
    ");
    $stmt->execute([$cutoffDate]);
    
    return $stmt->rowCount();
}

// ==========================================
// REPORT GENERATION FUNCTIONS
// ==========================================

// Generate productivity report
function generateProductivityReport($conn, $startDate, $endDate) {
    $stmt = $conn->prepare("
        SELECT 
            e.emp_code,
            e.name,
            SUM(t.total_time) as total_time,
            SUM(t.productive_time) as productive_time,
            SUM(t.idle_time) as idle_time,
            ROUND((SUM(t.productive_time) / SUM(t.total_time)) * 100, 2) as productivity_percent
        FROM tracking_sessions t
        JOIN employees e ON t.employee_id = e.id
        WHERE t.date BETWEEN ? AND ?
        GROUP BY e.id
        ORDER BY productivity_percent DESC
    ");
    $stmt->execute([$startDate, $endDate]);
    return $stmt->fetchAll();
}

// Generate attendance report
function generateAttendanceReport($conn, $startDate, $endDate) {
    $stmt = $conn->prepare("
        SELECT 
            e.emp_code,
            e.name,
            COUNT(DISTINCT t.date) as days_present,
            DATEDIFF(?, ?) + 1 as total_days,
            SUM(t.total_time) as total_work_time
        FROM employees e
        LEFT JOIN tracking_sessions t ON e.id = t.employee_id AND t.date BETWEEN ? AND ?
        WHERE e.status = 'active'
        GROUP BY e.id
        ORDER BY e.name
    ");
    $stmt->execute([$endDate, $startDate, $startDate, $endDate]);
    return $stmt->fetchAll();
}

// ==========================================
// ACTIVITY LOG FUNCTIONS
// ==========================================

// Log admin activity
function logActivity($conn, $employeeId, $activityType, $description) {
    $stmt = $conn->prepare("
        INSERT INTO activity_logs (employee_id, activity_type, description) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$employeeId, $activityType, $description]);
}

// Get recent activities
function getRecentActivities($conn, $limit = 10) {
    $stmt = $conn->prepare("
        SELECT a.*, e.name as employee_name, e.emp_code
        FROM activity_logs a
        JOIN employees e ON a.employee_id = e.id
        ORDER BY a.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// ==========================================
// VALIDATION FUNCTIONS
// ==========================================

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Validate date format
function isValidDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// ==========================================
// PAGINATION FUNCTIONS
// ==========================================

// Calculate pagination
function getPagination($totalRecords, $perPage = 20, $currentPage = 1) {
    $totalPages = ceil($totalRecords / $perPage);
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total_records' => $totalRecords,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'per_page' => $perPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

// ==========================================
// EXPORT FUNCTIONS
// ==========================================

// Export to CSV
function exportToCSV($data, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($data)) {
        // Add headers
        fputcsv($output, array_keys($data[0]));
        
        // Add data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}

// ==========================================
// NOTIFICATION FUNCTIONS
// ==========================================

// Send email notification
function sendEmailNotification($to, $subject, $message) {
    $headers = "From: noreply@desktracker.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// ==========================================
// SYSTEM SETTINGS
// ==========================================

// Get system settings
function getSystemSettings($conn) {
    $stmt = $conn->query("SELECT * FROM system_settings");
    $settings = [];
    
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    return $settings;
}

// Update system setting
function updateSystemSetting($conn, $key, $value) {
    $stmt = $conn->prepare("
        INSERT INTO system_settings (setting_key, setting_value) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = ?
    ");
    $stmt->execute([$key, $value, $value]);
}
?>
