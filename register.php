<?php
// register.php - Fixed version with proper ambassador referral handling
define('SECURE_ACCESS', true);

// Include config file (stored outside public directory)
require_once '../config.php'; // Adjust path as needed

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Connect to database using constants from config
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", 
                   DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Get the JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Log incoming data for debugging
    error_log("=== REGISTRATION DEBUG START ===");
    error_log("Raw input: " . $input);
    error_log("Decoded data: " . print_r($data, true));
    
    // Validate required fields
    if (!$data || !isset($data['teamName']) || !isset($data['schoolName']) || !isset($data['member1'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    // Sanitize the data
    function sanitizeString($str) {
        return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
    }
    
    function sanitizeEmail($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
    
    // Handle ambassador referral - FIXED to properly handle the ambassadorId field
    $ambassadorId = null;
    if (isset($data['ambassadorId']) && !empty($data['ambassadorId'])) {
        $ambassadorInput = trim($data['ambassadorId']);
        error_log("Ambassador input received: " . $ambassadorInput);
        
        // Since the HTML form sends the ambassador_id value, we expect a numeric ID
        if (is_numeric($ambassadorInput)) {
            $ambassadorId = (int)$ambassadorInput;
            
            // Verify the ambassador exists and is active
            $ambassadorCheck = $pdo->prepare("
                SELECT ambassador_id, name FROM ambassadors 
                WHERE ambassador_id = ? AND status = 'active'
            ");
            $ambassadorCheck->execute([$ambassadorId]);
            $ambassadorResult = $ambassadorCheck->fetch();
            
            if ($ambassadorResult) {
                error_log("Valid ambassador found: ID {$ambassadorId}, Name: " . $ambassadorResult['name']);
            } else {
                error_log("No active ambassador found for ID: {$ambassadorId}");
                $ambassadorId = null; // Invalid ambassador, set to null
            }
        } else {
            error_log("Non-numeric ambassador input received: {$ambassadorInput}");
            $ambassadorId = null;
        }
    } else {
        error_log("No ambassador referral provided");
    }
    
    // Sanitize team data
    $teamName = sanitizeString($data['teamName']);
    $schoolName = sanitizeString($data['schoolName']);
    $registrationDate = date('Y-m-d H:i:s');
    
    // Sanitize member 1 data (required)
    $member1 = [
        'name' => sanitizeString($data['member1']['name']),
        'grade' => sanitizeString($data['member1']['grade']),
        'birthDate' => sanitizeString($data['member1']['birthDate']),
        'email' => sanitizeEmail($data['member1']['email']),
        'phone' => sanitizeString($data['member1']['phone'])
    ];
    
    // Validate member 1 email
    if (!filter_var($member1['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email address for member 1']);
        exit;
    }
    
    // Sanitize optional member 2 data
    $member2 = null;
    if (isset($data['member2']) && !empty($data['member2']['name'])) {
        $member2 = [
            'name' => sanitizeString($data['member2']['name']),
            'grade' => sanitizeString($data['member2']['grade']),
            'birthDate' => sanitizeString($data['member2']['birthDate']),
            'email' => sanitizeEmail($data['member2']['email']),
            'phone' => sanitizeString($data['member2']['phone'])
        ];
        
        // Validate member 2 email if provided
        if (!empty($member2['email']) && !filter_var($member2['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email address for member 2']);
            exit;
        }
    }
    
    // Sanitize optional member 3 data
    $member3 = null;
    if (isset($data['member3']) && !empty($data['member3']['name'])) {
        $member3 = [
            'name' => sanitizeString($data['member3']['name']),
            'grade' => sanitizeString($data['member3']['grade']),
            'birthDate' => sanitizeString($data['member3']['birthDate']),
            'email' => sanitizeEmail($data['member3']['email']),
            'phone' => sanitizeString($data['member3']['phone'])
        ];
        
        // Validate member 3 email if provided
        if (!empty($member3['email']) && !filter_var($member3['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email address for member 3']);
            exit;
        }
    }
    
    // Start database transaction
    $pdo->beginTransaction();
    
    try {
        // Insert team data with ambassador reference
        $teamStmt = $pdo->prepare("
            INSERT INTO teams (team_name, school_name, registration_date, user_ip, referred_by_ambassador) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $teamResult = $teamStmt->execute([
            $teamName, 
            $schoolName, 
            $registrationDate, 
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $ambassadorId
        ]);
        
        if (!$teamResult) {
            throw new Exception('Failed to insert team data');
        }
        
        $teamId = $pdo->lastInsertId();
        error_log("Team inserted successfully with ID: {$teamId}");
        
        // Insert member 1 (commander)
        $memberStmt = $pdo->prepare("
            INSERT INTO team_members (team_id, member_role, name, grade, birth_date, email, phone) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $memberStmt->execute([
            $teamId, 
            'commander', 
            $member1['name'], 
            $member1['grade'], 
            $member1['birthDate'], 
            $member1['email'], 
            $member1['phone']
        ]);
        
        // Insert member 2 if exists (officer)
        if ($member2) {
            $memberStmt->execute([
                $teamId, 
                'officer', 
                $member2['name'], 
                $member2['grade'], 
                $member2['birthDate'], 
                $member2['email'], 
                $member2['phone']
            ]);
            error_log("Member 2 (officer) inserted for team {$teamId}");
        }
        
        // Insert member 3 if exists (specialist)
        if ($member3) {
            $memberStmt->execute([
                $teamId, 
                'specialist', 
                $member3['name'], 
                $member3['grade'], 
                $member3['birthDate'], 
                $member3['email'], 
                $member3['phone']
            ]);
            error_log("Member 3 (specialist) inserted for team {$teamId}");
        }
        
        // Update ambassador referral count ONLY if we have a valid ambassador
        $ambassadorUpdated = false;
        if ($ambassadorId) {
            $updateAmbassador = $pdo->prepare("
                UPDATE ambassadors 
                SET referral_count = referral_count + 1 
                WHERE ambassador_id = ? AND status = 'active'
            ");
            $ambassadorUpdated = $updateAmbassador->execute([$ambassadorId]);
            
            if ($ambassadorUpdated && $updateAmbassador->rowCount() > 0) {
                error_log("Ambassador referral count updated successfully for ID: {$ambassadorId}");
                
                // Verify the update
                $verifyStmt = $pdo->prepare("SELECT referral_count FROM ambassadors WHERE ambassador_id = ?");
                $verifyStmt->execute([$ambassadorId]);
                $newCount = $verifyStmt->fetchColumn();
                error_log("Ambassador {$ambassadorId} new referral count: {$newCount}");
            } else {
                error_log("Failed to update ambassador referral count for ID: {$ambassadorId}");
                $ambassadorId = null; // Reset if update failed
            }
        }
        
        // Commit the transaction
        $pdo->commit();
        error_log("Transaction committed successfully");
        
        // Log successful registration with ambassador info
        $logMessage = "Registration completed successfully:";
        $logMessage .= " Team ID: {$teamId}";
        $logMessage .= ", Team Name: {$teamName}";
        $logMessage .= ", School: {$schoolName}";
        $logMessage .= ", Members: " . ($member2 ? ($member3 ? "3" : "2") : "1");
        if ($ambassadorId) {
            $logMessage .= ", Ambassador Referral: ID {$ambassadorId} (Updated: " . ($ambassadorUpdated ? "YES" : "NO") . ")";
        } else {
            $logMessage .= ", No Ambassador Referral";
        }
        error_log($logMessage);
        error_log("=== REGISTRATION DEBUG END ===");
        
        // Return success response
        echo json_encode([
            'success' => true, 
            'message' => 'Team registration completed successfully', 
            'team_id' => $teamId,
            'ambassador_id' => $ambassadorId,
            'ambassador_updated' => $ambassadorUpdated
        ]);
        
    } catch (Exception $e) {
        // Rollback the transaction on error
        $pdo->rollback();
        error_log("Transaction rolled back due to error: " . $e->getMessage());
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    error_log('SQL State: ' . $e->getCode());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred',
        'debug' => $e->getMessage() // Remove this in production
    ]);
} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Registration failed: ' . $e->getMessage()
    ]);
}
?>