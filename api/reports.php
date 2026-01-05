<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
session_start();

include '../config/db.php';

$report_type = $_GET['type'] ?? '';
$employee_id = $_GET['employee_id'] ?? null;
$date = $_GET['date'] ?? date('Y-m-d');

try {
    switch ($report_type) {
        
        // ==========================================
        // COMPANY PRODUCTIVITY (Dashboard)
        // ==========================================
        case 'company_productivity':
            $stmt = $conn->query("SELECT COUNT(*) as total_users FROM employees WHERE status = 'active'");
            $total_users = $stmt->fetch()['total_users'];
            
            $stmt = $conn->prepare("SELECT COUNT(DISTINCT employee_id) as present_users FROM tracking_sessions WHERE date = ?");
            $stmt->execute([$date]);
            $present_users = $stmt->fetch()['present_users'];
            
            $absent_users = $total_users - $present_users;
            
            $stmt = $conn->prepare("SELECT SUM(productive_time) as total_productive, SUM(total_time) as total_time FROM tracking_sessions WHERE date = ?");
            $stmt->execute([$date]);
            $result = $stmt->fetch();
            $productivity = ($result['total_time'] > 0) ? round(($result['total_productive'] / $result['total_time']) * 100, 2) : 0;
            
            echo json_encode([
                'total_users' => $total_users,
                'present_users' => $present_users,
                'absent_users' => $absent_users,
                'productivity' => $productivity
            ]);
            break;
        
        // ==========================================
        // EMPLOYEE DETAILS (User Tracking, Screenshots)
        // ==========================================
        case 'employee_details':
            $stmt = $conn->prepare("SELECT * FROM tracking_sessions WHERE employee_id = ? AND date = ?");
            $stmt->execute([$employee_id, $date]);
            $tracking = $stmt->fetch();
            
            $screenshots = [];
            $urls = [];
            
            if ($tracking) {
                // Get URL logs
                $stmt = $conn->prepare("SELECT url_title, time_spent, category FROM url_logs WHERE tracking_session_id = ? ORDER BY time_spent DESC LIMIT 10");
                $stmt->execute([$tracking['id']]);
                $urls = $stmt->fetchAll();
                
                // Get screenshots
                $stmt = $conn->prepare("SELECT image_path, captured_at FROM screenshots WHERE tracking_session_id = ? ORDER BY captured_at DESC");
                $stmt->execute([$tracking['id']]);
                $screenshots = $stmt->fetchAll();
            }
            
            echo json_encode([
                'tracking' => $tracking,
                'urls' => $urls,
                'screenshots' => $screenshots
            ]);
            break;
        
        // ==========================================
        // BREAK LOGS (User Tracking Report)
        // ==========================================
        case 'break_logs':
            $stmt = $conn->prepare("
                SELECT * FROM break_logs 
                WHERE employee_id = ? AND date = ?
                ORDER BY start_time DESC
            ");
            $stmt->execute([$employee_id, $date]);
            echo json_encode($stmt->fetchAll());
            break;

        // ==========================================
        // TOP PRODUCTIVE USERS (Dashboard)
        // ==========================================
        case 'top_users':
            $stmt = $conn->prepare("
                SELECT e.name, e.emp_code, t.productive_time, t.total_time,
                ROUND((t.productive_time / t.total_time) * 100, 2) as productivity_percent
                FROM tracking_sessions t
                JOIN employees e ON t.employee_id = e.id
                WHERE t.date = ?
                ORDER BY productivity_percent DESC
                LIMIT 5
            ");
            $stmt->execute([$date]);
            echo json_encode($stmt->fetchAll());
            break;
        
        // ==========================================
        // LOGIN SESSIONS (User Tracking Report)
        // ==========================================
        case 'login_sessions':
            $stmt = $conn->prepare("
                SELECT * FROM login_sessions 
                WHERE employee_id = ? AND DATE(login_time) = ?
                ORDER BY login_time DESC
            ");
            $stmt->execute([$employee_id, $date]);
            echo json_encode($stmt->fetchAll());
            break;
        
        // ==========================================
        // APPLICATION USAGE (Application Usage Report)
        // ==========================================
        case 'application_usage':
            if ($employee_id) {
                $stmt = $conn->prepare("
                    SELECT a.app_name, SUM(a.time_spent) as time_spent
                    FROM application_logs a
                    JOIN tracking_sessions t ON a.tracking_session_id = t.id
                    WHERE t.employee_id = ? AND t.date = ?
                    GROUP BY a.app_name
                    ORDER BY time_spent DESC
                    LIMIT 10
                ");
                $stmt->execute([$employee_id, $date]);
            } else {
                $stmt = $conn->prepare("
                    SELECT a.app_name, SUM(a.time_spent) as time_spent
                    FROM application_logs a
                    JOIN tracking_sessions t ON a.tracking_session_id = t.id
                    WHERE t.date = ?
                    GROUP BY a.app_name
                    ORDER BY time_spent DESC
                    LIMIT 10
                ");
                $stmt->execute([$date]);
            }
            echo json_encode($stmt->fetchAll());
            break;
        
        // ==========================================
        // DAILY ATTENDANCE (Attendance Report)
        // ==========================================
        case 'daily_attendance':
            // Get all employees
            $stmt = $conn->query("SELECT id, emp_code, name FROM employees WHERE status = 'active'");
            $employees = $stmt->fetchAll();
            
            $attendance = [];
            $presentCount = 0;
            
            foreach ($employees as $emp) {
                // Get first login and last logout
                $stmt = $conn->prepare("
                    SELECT 
                        MIN(login_time) as login_time,
                        MAX(logout_time) as logout_time,
                        SUM(session_duration) as total_seconds
                    FROM login_sessions 
                    WHERE employee_id = ? AND DATE(login_time) = ?
                ");
                $stmt->execute([$emp['id'], $date]);
                $session = $stmt->fetch();
                
                $totalHours = 0;
                if ($session && $session['total_seconds']) {
                    $totalHours = round($session['total_seconds'] / 3600, 2);
                }
                
                if ($session['login_time']) {
                    $presentCount++;
                }
                
                $attendance[] = [
                    'emp_code' => $emp['emp_code'],
                    'name' => $emp['name'],
                    'login_time' => $session['login_time'] ?? null,
                    'logout_time' => $session['logout_time'] ?? null,
                    'total_hours' => $totalHours
                ];
            }
            
            echo json_encode([
                'summary' => [
                    'total' => count($employees),
                    'present' => $presentCount,
                    'absent' => count($employees) - $presentCount
                ],
                'attendance' => $attendance
            ]);
            break;
        
        // ==========================================
        // IDLE TIME ANALYSIS (Idle Time Report)
        // ==========================================
        case 'idle_analysis':
            $stmt = $conn->prepare("
                SELECT 
                    e.id, e.name, e.emp_code,
                    t.idle_time,
                    t.total_time,
                    ROUND((t.idle_time / t.total_time) * 100, 2) as idle_percentage
                FROM tracking_sessions t
                JOIN employees e ON t.employee_id = e.id
                WHERE t.date = ? AND t.total_time > 0
                ORDER BY t.idle_time DESC
            ");
            $stmt->execute([$date]);
            echo json_encode($stmt->fetchAll());
            break;
        
        // ==========================================
        // URL TRACKING (Website Usage)
        // ==========================================
        case 'url_tracking':
            if ($employee_id) {
                $stmt = $conn->prepare("
                    SELECT u.url_title, SUM(u.time_spent) as time_spent, u.category
                    FROM url_logs u
                    JOIN tracking_sessions t ON u.tracking_session_id = t.id
                    WHERE t.employee_id = ? AND t.date = ?
                    GROUP BY u.url_title, u.category
                    ORDER BY time_spent DESC
                    LIMIT 15
                ");
                $stmt->execute([$employee_id, $date]);
            } else {
                $stmt = $conn->prepare("
                    SELECT u.url_title, SUM(u.time_spent) as time_spent, u.category
                    FROM url_logs u
                    JOIN tracking_sessions t ON u.tracking_session_id = t.id
                    WHERE t.date = ?
                    GROUP BY u.url_title, u.category
                    ORDER BY time_spent DESC
                    LIMIT 10
                ");
                $stmt->execute([$date]);
            }
            echo json_encode($stmt->fetchAll());
            break;
        
        // ==========================================
        // PRODUCTIVITY TREND (7 Days)
        // ==========================================
        case 'productivity_trend':
            $stmt = $conn->prepare("
                SELECT 
                    date,
                    ROUND((SUM(productive_time) / SUM(total_time)) * 100, 2) as productivity
                FROM tracking_sessions
                WHERE date >= DATE_SUB(?, INTERVAL 7 DAY) AND date <= ?
                GROUP BY date
                ORDER BY date ASC
            ");
            $stmt->execute([$date, $date]);
            echo json_encode($stmt->fetchAll());
            break;
        
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid report type']);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
