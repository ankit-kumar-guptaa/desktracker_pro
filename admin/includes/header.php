<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeskTracker Pro - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-bg: #2d3748;
            --sidebar-hover: #4a5568;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7fafc;
        }
        
        .sidebar {
            background: var(--sidebar-bg);
            min-height: 100vh;
            width: 260px;
            position: fixed;
            top: 0;
            left: 0;
            padding: 20px 0;
            color: white;
            overflow-y: auto;
        }
        
        .sidebar-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-brand h4 {
            color: white;
            font-weight: 700;
            margin: 0;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li a {
            display: block;
            padding: 12px 25px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: var(--sidebar-hover);
            color: white;
            border-left: 4px solid var(--primary-color);
        }
        
        .sidebar-menu li a i {
            width: 25px;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
        
        .top-navbar {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .stats-card-blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .stats-card-green { background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: white; }
        .stats-card-orange { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; }
        .stats-card-red { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
        
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .user-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background: #f7fafc;
            border-radius: 50px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <h4>🖥️ DeskTracker</h4>
            <small>Admin Panel</small>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="employees" class="<?php echo basename($_SERVER['PHP_SELF']) == 'employees.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Manage Employees</a></li>
            <li><a href="#" data-bs-toggle="collapse" data-bs-target="#reportsMenu"><i class="fas fa-chart-bar"></i> Reports <i class="fas fa-chevron-down float-end"></i></a>
                <ul class="collapse list-unstyled ps-4" id="reportsMenu">
                    <li><a href="reports/user_tracking.php"><i class="fas fa-user-clock"></i> User Tracking</a></li>
                    <li><a href="reports/application_usage.php"><i class="fas fa-desktop"></i> Application Usage</a></li>
                    <li><a href="reports/screenshot_report.php"><i class="fas fa-camera"></i> Screenshots</a></li>
                    <li><a href="reports/daily_attendance.php"><i class="fas fa-calendar-check"></i> Daily Attendance</a></li>
                    <li><a href="reports/idle_time_analysis.php"><i class="fas fa-clock"></i> Idle Time Analysis</a></li>
                </ul>
            </li>
            <li><a href="settings" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="top-navbar">
            <div>
                <h5 class="mb-0">Welcome, Admin</h5>
                <small class="text-muted">Dashboard Overview</small>
            </div>
            <div class="user-badge">
                <div class="user-avatar">A</div>
                <span><?php echo $_SESSION['admin_username']; ?></span>
            </div>
        </div>
