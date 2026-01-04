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
            --primary-color: #003366; /* Navy Blue */
            --secondary-color: #d32f2f; /* Red */
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --sidebar-bg: #003366;
            --sidebar-hover: #004080;
            --bg-color: #f1f5f9;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-light: #64748b;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-color);
            color: var(--text-main);
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
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
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
            letter-spacing: 1px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 14px 25px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
            border-left: 4px solid transparent;
        }
        
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--secondary-color);
        }
        
        .sidebar-menu li a i {
            width: 25px;
            margin-right: 10px;
            text-align: center;
        }
        
        .main-content {
            margin-left: 260px;
            padding: 30px;
        }
        
        .top-navbar {
            background: var(--card-bg);
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 5px solid var(--secondary-color);
        }
        
        .stats-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid rgba(0,0,0,0.05);
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .stats-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .stats-card-blue { background: rgba(0, 51, 102, 0.1); color: var(--primary-color); }
        .stats-card-green { background: rgba(16, 185, 129, 0.1); color: var(--success-color); }
        .stats-card-orange { background: rgba(245, 158, 11, 0.1); color: var(--warning-color); }
        .stats-card-red { background: rgba(211, 47, 47, 0.1); color: var(--secondary-color); }
        
        .chart-container {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .user-badge {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: #f8fafc;
            border-radius: 50px;
            border: 1px solid #e2e8f0;
        }
        
        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: var(--primary-color);
        }
    </style>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <h4>🖥️ DeskTracker</h4>
            <small>Admin Panel</small>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="employees.php"><i class="fas fa-users"></i> Manage Employees</a></li>
            <li><a href="#" data-bs-toggle="collapse" data-bs-target="#reportsMenu"><i class="fas fa-chart-bar"></i> Reports <i class="fas fa-chevron-down float-end"></i></a>
                <ul class="collapse list-unstyled ps-4" id="reportsMenu">
                    <li><a href="reports/user_tracking.php"><i class="fas fa-user-clock"></i> User Tracking</a></li>
                    <li><a href="reports/application_usage.php"><i class="fas fa-desktop"></i> Application Usage</a></li>
                    <li><a href="reports/screenshot_report.php"><i class="fas fa-camera"></i> Screenshots</a></li>
                    <li><a href="reports/daily_attendance.php"><i class="fas fa-calendar-check"></i> Daily Attendance</a></li>
                    <li><a href="reports/idle_time_analysis.php"><i class="fas fa-clock"></i> Idle Time Analysis</a></li>
                </ul>
            </li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
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
