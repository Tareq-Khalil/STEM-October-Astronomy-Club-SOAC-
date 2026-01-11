<?php
// get_ambassadors.php - API to fetch active ambassadors
define('SECURE_ACCESS', true);

require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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
    
    // Get active ambassadors ordered by name
    $stmt = $pdo->prepare("
        SELECT ambassador_id, name, referral_count 
        FROM ambassadors 
        WHERE status = 'active' 
        ORDER BY name ASC
    ");
    
    $stmt->execute();
    $ambassadors = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'ambassadors' => $ambassadors
    ]);
    
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error occurred']);
} catch (Exception $e) {
    error_log('Get ambassadors error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch ambassadors']);
}
?>