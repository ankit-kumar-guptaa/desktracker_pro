<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$versionFile = 'version.json';

if (file_exists($versionFile)) {
    echo file_get_contents($versionFile);
} else {
    echo json_encode([
        "latest_version" => "1.0.0",
        "force_update" => false,
        "download_url" => "",
        "message" => "Version file not found."
    ]);
}
?>