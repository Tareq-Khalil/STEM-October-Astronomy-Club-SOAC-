<?php
// File: api/instructor.php
// Enhanced API endpoints for instructor operations

require_once '../config.php';

$conn = getDBConnection();
if (!$conn) {
    handleError("Database connection failed", 500);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create_room':
        if ($method === 'POST') {
            createRoom($conn);
        }
        break;
    
    case 'get_room':
        if ($method === 'GET') {
            getRoom($conn);
        }
        break;
    
    case 'start_question':
        if ($method === 'POST') {
            startQuestion($conn);
        }
        break;
    
    case 'submit_answer':
        if ($method === 'POST') {
            submitAnswer($conn);
        }
        break;
    
    case 'next_question':
        if ($method === 'POST') {
            nextQuestion($conn);
        }
        break;
    
    case 'declare_winner':
        if ($method === 'POST') {
            declareWinner($conn);
        }
        break;
    
    case 'get_questions':
        if ($method === 'GET') {
            getQuestions($conn);
        }
        break;
    
    case 'adjust_score':
        if ($method === 'POST') {
            adjustScore($conn);
        }
        break;
    
    case 'allow_other_team':
        if ($method === 'POST') {
            allowOtherTeam($conn);
        }
        break;
    
    case 'reset_buzzers':
        if ($method === 'POST') {
            resetBuzzers($conn);
        }
        break;
    
    default:
        handleError("Invalid action");
}

function createRoom($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $instructor_name = $data['instructor_name'] ?? '';
    $instructor_password = $data['instructor_password'] ?? '';
    $wave_id = $data['wave_id'] ?? 1;
    $team1_id = $data['team1_id'] ?? null;
    $team2_id = $data['team2_id'] ?? null;
    
    if (empty($instructor_name) || empty($instructor_password)) {
        handleError("Instructor name and password required");
    }
    
    // Generate unique room code
    $room_code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
    
    $hashed_password = password_hash($instructor_password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO rooms (room_code, wave_id, instructor_name, instructor_password, team1_id, team2_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissii", $room_code, $wave_id, $instructor_name, $hashed_password, $team1_id, $team2_id);
    
    if ($stmt->execute()) {
        $room_id = $stmt->insert_id;
        
        // Initialize room state
        $stmt2 = $conn->prepare("INSERT INTO room_state (room_id) VALUES (?)");
        $stmt2->bind_param("i", $room_id);
        $stmt2->execute();
        
        // Update tournament bracket with room info
        $stmt3 = $conn->prepare("UPDATE tournament_bracket SET room_id = ?, match_status = 'in_progress' WHERE wave_id = ? AND team1_id = ? AND team2_id = ?");
        $stmt3->bind_param("iiii", $room_id, $wave_id, $team1_id, $team2_id);
        $stmt3->execute();
        
        sendJSON([
            'success' => true,
            'room_code' => $room_code,
            'room_id' => $room_id
        ]);
    } else {
        handleError("Failed to create room");
    }
}

function getRoom($conn) {
    $room_code = $_GET['room_code'] ?? '';
    
    if (empty($room_code)) {
        handleError("Room code required");
    }
    
    $stmt = $conn->prepare("
        SELECT r.*, 
               t1.team_name as team1_name, t1.team_code as team1_code,
               t2.team_name as team2_name, t2.team_code as team2_code,
               rs.current_question, rs.buzzer_locked, rs.buzzed_team_id, 
               rs.waiting_for_answer, rs.answer_phase_team_id, rs.question_start_time
        FROM rooms r
        LEFT JOIN teams t1 ON r.team1_id = t1.id
        LEFT JOIN teams t2 ON r.team2_id = t2.id
        LEFT JOIN room_state rs ON r.id = rs.room_id
        WHERE r.room_code = ?
    ");
    $stmt->bind_param("s", $room_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        sendJSON(['success' => true, 'room' => $row]);
    } else {
        handleError("Room not found", 404);
    }
}

function startQuestion($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $room_id = $data['room_id'] ?? 0;
    $question_number = $data['question_number'] ?? 0;
    
    // Update room state
    $stmt = $conn->prepare("
        UPDATE room_state 
        SET current_question = ?, 
            question_start_time = NOW(), 
            buzzer_locked = FALSE, 
            buzzed_team_id = NULL,
            waiting_for_answer = FALSE,
            answer_phase_team_id = NULL,
            answer_time_remaining = 30,
            last_answer_team_id = NULL,
            last_answer_correct = NULL,
            last_answer_given = NULL,
            last_answer_time = NULL,
            locked_out_team_id = NULL
        WHERE room_id = ?
    ");
    $stmt->bind_param("ii", $question_number, $room_id);
    
    if ($stmt->execute()) {
        sendJSON(['success' => true, 'message' => 'Question started']);
    } else {
        handleError("Failed to start question");
    }
}

function submitAnswer($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $room_id = $data['room_id'] ?? 0;
    $team_id = $data['team_id'] ?? 0;
    $answer = $data['answer'] ?? '';
    $question_number = $data['question_number'] ?? 0;
    
    // Get room and question info
    $stmt = $conn->prepare("
        SELECT r.wave_id, r.team1_id, r.team2_id, q.correct_answer, rs.question_start_time,
               t1.team_name as team1_name, t2.team_name as team2_name
        FROM rooms r
        JOIN room_state rs ON r.id = rs.room_id
        JOIN questions q ON q.wave_id = r.wave_id AND q.question_number = ?
        LEFT JOIN teams t1 ON r.team1_id = t1.id
        LEFT JOIN teams t2 ON r.team2_id = t2.id
        WHERE r.id = ?
    ");
    $stmt->bind_param("ii", $question_number, $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $question_data = $result->fetch_assoc();
    
    if (!$question_data) {
        handleError("Question not found");
    }
    
    $is_correct = ($answer === $question_data['correct_answer']);
    $time_taken = time() - strtotime($question_data['question_start_time']);
    
    // Get team name
    $team_name = ($team_id == $question_data['team1_id']) ? $question_data['team1_name'] : $question_data['team2_name'];
    
    // Record buzz activity
    $stmt = $conn->prepare("
        INSERT INTO buzz_activity (room_id, question_number, team_id, answer_given, is_correct, time_taken_seconds)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiisid", $room_id, $question_number, $team_id, $answer, $is_correct, $time_taken);
    $stmt->execute();
    
    // Update room state with last answer info
    $stmt = $conn->prepare("
        UPDATE room_state 
        SET last_answer_team_id = ?,
            last_answer_correct = ?,
            last_answer_given = ?,
            last_answer_time = NOW()
        WHERE room_id = ?
    ");
    $stmt->bind_param("iisi", $team_id, $is_correct, $answer, $room_id);
    $stmt->execute();
    
    // Update score if correct
    if ($is_correct) {
        if ($team_id == $question_data['team1_id']) {
            $conn->query("UPDATE rooms SET team1_score = team1_score + 1 WHERE id = $room_id");
        } else {
            $conn->query("UPDATE rooms SET team2_score = team2_score + 1 WHERE id = $room_id");
        }
    } else {
        // If wrong, lock this team out until next question
        $stmt = $conn->prepare("
            UPDATE room_state 
            SET locked_out_team_id = ?
            WHERE room_id = ?
        ");
        $stmt->bind_param("ii", $team_id, $room_id);
        $stmt->execute();
    }
    
    sendJSON([
        'success' => true,
        'is_correct' => $is_correct,
        'correct_answer' => $question_data['correct_answer'],
        'team_name' => $team_name,
        'answer_given' => $answer
    ]);
}

function nextQuestion($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $room_id = $data['room_id'] ?? 0;
    
    $stmt = $conn->prepare("
        UPDATE room_state 
        SET current_question = current_question + 1,
            buzzer_locked = FALSE,
            buzzed_team_id = NULL,
            waiting_for_answer = FALSE,
            answer_phase_team_id = NULL,
            question_start_time = NULL,
            last_answer_team_id = NULL,
            last_answer_correct = NULL,
            last_answer_given = NULL,
            last_answer_time = NULL,
            locked_out_team_id = NULL
        WHERE room_id = ?
    ");
    $stmt->bind_param("i", $room_id);
    
    if ($stmt->execute()) {
        sendJSON(['success' => true]);
    } else {
        handleError("Failed to move to next question");
    }
}

function declareWinner($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $room_id = $data['room_id'] ?? 0;
    $winner_team_id = $data['winner_team_id'] ?? 0;
    
    $conn->begin_transaction();
    
    try {
        // Update room
        $stmt = $conn->prepare("UPDATE rooms SET winner_team_id = ?, room_status = 'completed' WHERE id = ?");
        $stmt->bind_param("ii", $winner_team_id, $room_id);
        $stmt->execute();
        
        // Update tournament bracket
        $stmt = $conn->prepare("UPDATE tournament_bracket SET winner_team_id = ?, match_status = 'completed' WHERE room_id = ?");
        $stmt->bind_param("ii", $winner_team_id, $room_id);
        $stmt->execute();
        
        $conn->commit();
        sendJSON(['success' => true, 'message' => 'Winner declared']);
    } catch (Exception $e) {
        $conn->rollback();
        handleError("Failed to declare winner");
    }
}

function getQuestions($conn) {
    $wave_id = $_GET['wave_id'] ?? 1;
    
    $stmt = $conn->prepare("SELECT * FROM questions WHERE wave_id = ? ORDER BY question_number");
    $stmt->bind_param("i", $wave_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
    
    sendJSON(['success' => true, 'questions' => $questions]);
}

function adjustScore($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $room_id = $data['room_id'] ?? 0;
    $team_number = $data['team_number'] ?? 0; // 1 or 2
    $adjustment = $data['adjustment'] ?? 0; // +1 or -1
    
    if ($team_number == 1) {
        $stmt = $conn->prepare("UPDATE rooms SET team1_score = GREATEST(0, team1_score + ?) WHERE id = ?");
    } else {
        $stmt = $conn->prepare("UPDATE rooms SET team2_score = GREATEST(0, team2_score + ?) WHERE id = ?");
    }
    
    $stmt->bind_param("ii", $adjustment, $room_id);
    
    if ($stmt->execute()) {
        sendJSON(['success' => true, 'message' => 'Score adjusted']);
    } else {
        handleError("Failed to adjust score");
    }
}

function allowOtherTeam($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $room_id = $data['room_id'] ?? 0;
    $other_team_id = $data['other_team_id'] ?? 0;
    
    // Reset buzzer for the other team to answer
    $stmt = $conn->prepare("
        UPDATE room_state 
        SET buzzer_locked = FALSE,
            buzzed_team_id = NULL,
            waiting_for_answer = FALSE,
            answer_phase_team_id = ?
        WHERE room_id = ?
    ");
    $stmt->bind_param("ii", $other_team_id, $room_id);
    
    if ($stmt->execute()) {
        sendJSON(['success' => true, 'message' => 'Other team can now answer']);
    } else {
        handleError("Failed to allow other team");
    }
}

$conn->close();

?>