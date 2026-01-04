<?php
define('DB_HOST', 'srv1086.hstgr.io');
define('DB_USER', 'u141142577_desktracker');
define('DB_PASS', 'Ankit@1925050@');
define('DB_NAME', 'u141142577_desktracker');
// define('DB_HOST', 'localhost');
// define('DB_USER', 'root');
// define('DB_PASS', '');
// define('DB_NAME', 'desktracker_pro');

try {
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}
?>
