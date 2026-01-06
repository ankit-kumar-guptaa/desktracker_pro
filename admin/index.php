<?php
// Admin Routing System

// Handle PHP Built-in Server (for local development like php -S)
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $file = $_SERVER['DOCUMENT_ROOT'] . $path;
    if (file_exists($file) && !is_dir($file)) {
        return false; // serve file as is
    }
}

// Dynamic Base Path Detection
// This automatically adapts to whatever folder structure you have (localhost:8000/admin, localhost/project/admin, etc.)
$script_name = $_SERVER['SCRIPT_NAME'];
$script_dir = dirname($script_name);
$base_path = rtrim($script_dir, '/') . '/';

// Get the request URI
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path from request URI to get the route
if (strpos($request_uri, $base_path) === 0) {
    $route = substr($request_uri, strlen($base_path));
} else {
    // Fallback: if base path detection fails (e.g. nested folders mismatch), use the full URI
    $route = $request_uri;
}

$route = trim($route, '/');

// Default route
if ($route == '' || $route == 'index.php') {
    $route = 'dashboard';
}

// Define routes map
// Keys are the URL segments, Values are the actual file paths relative to this index.php
$routes = [
    'dashboard' => 'dashboard.php',
    'live_monitor' => 'live_monitor.php',
    'employees' => 'employees.php',
    'settings' => 'settings.php',
    'login' => 'login.php',
    'logout' => 'logout.php',
    
    // Reports
    'user_tracking' => 'reports/user_tracking.php',
    'application_usage' => 'reports/application_usage.php',
    'screenshot_report' => 'reports/screenshot_report.php',
    'daily_attendance' => 'reports/daily_attendance.php',
    'idle_time_analysis' => 'reports/idle_time_analysis.php',
];

// Robust Route Matching
// If the direct match fails, try to match the last segment (handles /admin/login vs login)
if (!array_key_exists($route, $routes)) {
    // Check for "admin/" prefix mismatch or other folder nesting
    $parts = explode('/', $route);
    $last_part = end($parts);
    
    if (array_key_exists($last_part, $routes)) {
        $route = $last_part;
    }
}

// Check if route exists
if (array_key_exists($route, $routes)) {
    $file_path = __DIR__ . '/' . $routes[$route];
    
    // Check if file exists before including
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        http_response_code(404);
        echo "404 - File Not Found: " . htmlspecialchars($routes[$route]);
    }
} else {
    // 404 Page
    http_response_code(404);
    echo "<div style='text-align:center; padding-top:50px; font-family:sans-serif;'>";
    echo "<h1>404 - Page Not Found</h1>";
    echo "<p>The requested URL was not found on this server.</p>";
    // echo "<p>Debug: Route requested: " . htmlspecialchars($route) . "</p>";
    echo "<p><a href='{$base_path}dashboard'>Go to Dashboard</a></p>";
    echo "</div>";
}
?>
