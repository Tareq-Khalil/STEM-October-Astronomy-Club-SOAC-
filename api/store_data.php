<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config12.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if ($data === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
        exit;
    }
    
    // Validate required fields
    $required_fields = [
        'sensor1' => ['co', 'no2', 'no', 'so2', 'voc', 'fan_status'],
        'sensor2' => ['co', 'no2', 'no', 'so2', 'voc', 'fan_status']
    ];
    
    foreach ($required_fields as $sensor => $fields) {
        if (!isset($data[$sensor])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Missing $sensor data"]);
            exit;
        }
        
        foreach ($fields as $field) {
            if (!isset($data[$sensor][$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => "Missing $sensor.$field data"]);
                exit;
            }
            
            // Validate numeric values
            if ($field !== 'fan_status' && !is_numeric($data[$sensor][$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => "Invalid $sensor.$field value"]);
                exit;
            }
        }
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO sensor_data (
                sensor1_co, sensor1_no2, sensor1_no, sensor1_so2, sensor1_voc, sensor1_fan_status,
                sensor2_co, sensor2_no2, sensor2_no, sensor2_so2, sensor2_voc, sensor2_fan_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            floatval($data['sensor1']['co']),
            floatval($data['sensor1']['no2']),
            floatval($data['sensor1']['no']),
            floatval($data['sensor1']['so2']),
            floatval($data['sensor1']['voc']),
            intval($data['sensor1']['fan_status']),
            floatval($data['sensor2']['co']),
            floatval($data['sensor2']['no2']),
            floatval($data['sensor2']['no']),
            floatval($data['sensor2']['so2']),
            floatval($data['sensor2']['voc']),
            intval($data['sensor2']['fan_status'])
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Data stored successfully']);
        
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    }
    
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // API endpoint to fetch data for the website
    try {
        $limit = isset($_GET['limit']) ? max(1, min(10000, intval($_GET['limit']))) : 720;
        
        $stmt = $pdo->prepare("
            SELECT * FROM sensor_data 
            ORDER BY timestamp DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $data = $stmt->fetchAll();
        
        // Reverse to get chronological order for charts
        $data = array_reverse($data);
        
        echo json_encode(['success' => true, 'data' => $data, 'count' => count($data)]);
        
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>