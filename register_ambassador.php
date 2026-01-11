<?php
// register_ambassador.php
define('SECURE_ACCESS', true);
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", 
                   DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Validate required fields
    if (!$data || !isset($data['name']) || !isset($data['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name and email are required']);
        exit;
    }
    
    function sanitizeString($str) {
        return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
    }
    
    function sanitizeEmail($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
    
    // Sanitize data
    $name = sanitizeString($data['name']);
    $email = sanitizeEmail($data['email']);
    $phone = isset($data['phone']) ? sanitizeString($data['phone']) : null;
    $schoolName = isset($data['schoolName']) ? sanitizeString($data['schoolName']) : null;
    $motivation = isset($data['motivation']) ? sanitizeString($data['motivation']) : null;
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email address']);
        exit;
    }
    
    // Check if email already exists
    $checkStmt = $pdo->prepare("SELECT ambassador_id FROM ambassadors WHERE email = ?");
    $checkStmt->execute([$email]);
    if ($checkStmt->fetch()) {
        http_response_code(400);
        echo json_encode(['error' => 'This email is already registered as an ambassador']);
        exit;
    }
    
    // Insert ambassador
    $stmt = $pdo->prepare("
        INSERT INTO ambassadors (name, email, phone, school_name, motivation, user_ip, registration_date) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $name,
        $email,
        $phone,
        $schoolName,
        $motivation,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    
    $ambassadorId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Ambassador registered successfully!',
        'ambassador_id' => $ambassadorId
    ]);
    
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error occurred']);
} catch (Exception $e) {
    error_log('Ambassador registration error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Registration failed']);
}
?>