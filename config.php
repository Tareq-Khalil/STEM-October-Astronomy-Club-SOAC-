<?php
// File: config.php
// Database configuration file

// Database credentials - UPDATE THESE WITH YOUR ACTUAL DATABASE INFO
define('DB_HOST', 'localhost');
define('DB_USER', 'soacewcn_cosmic2');
define('DB_PASS', 'cosmicquest6767@');
define('DB_NAME', 'soacewcn_cosmic_quest');

// Create database connection
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

// Helper function to send JSON response
function sendJSON($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Helper function to handle errors
function handleError($message, $code = 400) {
    sendJSON(['success' => false, 'error' => $message], $code);
}

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
session_start();

// Enable error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS headers for API requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
?>