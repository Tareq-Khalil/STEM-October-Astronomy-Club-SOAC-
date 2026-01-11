<?php
// File: api/team.php
// Enhanced API endpoints for team buzzer operations

require_once '../config.php';

$conn = getDBConnection();
if (!$conn) {
    handleError("Database connection failed", 500);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'join_room':
        if ($method === 'POST') {
            joinRoom($conn);
        }
        break;
    
    case 'buzz':
        if ($method === 'POST') {
            handleBuzz($conn);
        }
        break;
    
    case 'get_room_state':
        if ($method === 'GET') {
            getRoomState($conn);
        }
        break;
        
    case 'get_current_question':
        if ($method === 'GET') {
            getCurrentQuestion($conn);
        }
        break;
    
    default:
        handleError("Invalid action");
}

function joinRoom($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $room_code = $data['room_code'] ?? '';
    $team_code = $data['team_code'] ?? '';
    
    if (empty($room_code) || empty($team_code)) {
        handleError("Room code and team code required");
    }
    
    // Get team info
    $stmt = $conn->prepare("SELECT id, team_name FROM teams WHERE team_code = ?");
    $stmt->bind_param("s", $team_code);
    $stmt->execute();
    $team_result = $stmt->get_result();
    
    if ($team_result->num_rows === 0) {
        handleError("Invalid team code", 404);
    }
    
    $team = $team_result->fetch_assoc();
    
    // Get room info
    $stmt = $conn->prepare("
        SELECT r.*, 
               t1.team_name as team1_name, t1.id as team1_id,
               t2.team_name as team2_name, t2.id as team2_id
        FROM rooms r
        LEFT JOIN teams t1 ON r.team1_id = t1.id
        LEFT JOIN teams t2 ON r.team2_id = t2.id
        WHERE r.room_code = ?
    ");
    $stmt->bind_param("s", $room_code);
    $stmt->execute();
    $room_result = $stmt->get_result();
    
    if ($room_result->num_rows === 0) {
        handleError("Room not found", 404);
    }
    
    $room = $room_result->fetch_assoc();
    
    // Check if team is assigned to this room
    if ($room['team1_id'] != $team['id'] && $room['team2_id'] != $team['id']) {
        handleError("Team not assigned to this room", 403);
    }
    
    // Determine if this is team 1 or team 2
    $team_position = ($room['team1_id'] == $team['id']) ? 1 : 2;
    
    sendJSON([
        'success' => true,
        'team' => $team,
        'team_position' => $team_position,
        'room' => $room
    ]);
}

function handleBuzz($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $room_id = $data['room_id'] ?? 0;
    $team_id = $data['team_id'] ?? 0;
    
    if ($room_id === 0 || $team_id === 0) {
        handleError("Room ID and Team ID required");
    }
    
    // Start transaction for atomic buzz operation
    $conn->begin_transaction();
    
    try {
        // Check if buzzer is already locked or team is locked out
        $stmt = $conn->prepare("
            SELECT buzzer_locked, buzzed_team_id, current_question, locked_out_team_id
            FROM room_state 
            WHERE room_id = ? 
            FOR UPDATE
        ");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $state = $result->fetch_assoc();
        
        // Check if this team is locked out
        if ($state['locked_out_team_id'] == $team_id) {
            $conn->rollback();
            sendJSON([
                'success' => false,
                'message' => 'You answered incorrectly. Wait for the next question.',
                'locked_out' => true
            ]);
            return;
        }
        
        if ($state['buzzer_locked']) {
            $conn->rollback();
            sendJSON([
                'success' => false,
                'message' => 'Buzzer already pressed',
                'buzzed_team_id' => $state['buzzed_team_id']
            ]);
            return;
        }
        
        // Lock the buzzer for this team
        $stmt = $conn->prepare("
            UPDATE room_state 
            SET buzzer_locked = TRUE, 
                buzzed_team_id = ?,
                waiting_for_answer = TRUE,
                answer_phase_team_id = ?
            WHERE room_id = ?
        ");
        $stmt->bind_param("iii", $team_id, $team_id, $room_id);
        $stmt->execute();
        
        // Record the buzz
        $stmt = $conn->prepare("
            INSERT INTO buzz_activity (room_id, question_number, team_id, time_taken_seconds)
            SELECT ?, current_question, ?, TIMESTAMPDIFF(SECOND, question_start_time, NOW())
            FROM room_state
            WHERE room_id = ?
        ");
        $stmt->bind_param("iii", $room_id, $team_id, $room_id);
        $stmt->execute();
        
        $conn->commit();
        
        sendJSON([
            'success' => true,
            'message' => 'Buzz successful',
            'buzzed_team_id' => $team_id
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        handleError("Failed to process buzz: " . $e->getMessage());
    }
}

function getRoomState($conn) {
    $room_id = $_GET['room_id'] ?? 0;
    
    if ($room_id === 0) {
        handleError("Room ID required");
    }
    
    $stmt = $conn->prepare("
        SELECT rs.*, r.team1_score, r.team2_score, r.room_status, r.team1_id, r.team2_id,
               t1.team_name as team1_name, t2.team_name as team2_name
        FROM room_state rs
        JOIN rooms r ON rs.room_id = r.id
        LEFT JOIN teams t1 ON r.team1_id = t1.id
        LEFT JOIN teams t2 ON r.team2_id = t2.id
        WHERE rs.room_id = ?
    ");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Calculate time remaining
        if ($row['question_start_time']) {
            $elapsed = time() - strtotime($row['question_start_time']);
            $row['time_remaining'] = max(0, 30 - $elapsed);
        } else {
            $row['time_remaining'] = 30;
        }
        
        sendJSON(['success' => true, 'state' => $row]);
    } else {
        handleError("Room state not found", 404);
    }
}

function getCurrentQuestion($conn) {
    $room_id = $_GET['room_id'] ?? 0;
    
    if ($room_id === 0) {
        handleError("Room ID required");
    }
    
    $stmt = $conn->prepare("
        SELECT q.*
        FROM questions q
        JOIN rooms r ON q.wave_id = r.wave_id
        JOIN room_state rs ON rs.room_id = r.id
        WHERE r.id = ? AND q.question_number = rs.current_question
    ");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        sendJSON(['success' => true, 'question' => $row]);
    } else {
        sendJSON(['success' => false, 'message' => 'No current question']);
    }
}

$conn->close();
?>