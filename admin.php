<?php
// File: api/admin.php
// API endpoints for admin operations

require_once '../config.php';

$conn = getDBConnection();
if (!$conn) {
    handleError("Database connection failed", 500);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_teams':
        if ($method === 'GET') {
            getTeams($conn);
        }
        break;
    
    case 'get_bracket':
        if ($method === 'GET') {
            getBracket($conn);
        }
        break;
    
    case 'add_questions':
        if ($method === 'POST') {
            addQuestions($conn);
        }
        break;
    
    case 'get_waves':
        if ($method === 'GET') {
            getWaves($conn);
        }
        break;
    
    case 'update_bracket':
        if ($method === 'POST') {
            updateBracket($conn);
        }
        break;
    
    default:
        handleError("Invalid action");
}

function getTeams($conn) {
    $stmt = $conn->prepare("SELECT * FROM teams ORDER BY id");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $teams = [];
    while ($row = $result->fetch_assoc()) {
        $teams[] = $row;
    }
    
    sendJSON(['success' => true, 'teams' => $teams]);
}

function getBracket($conn) {
    $wave_id = $_GET['wave_id'] ?? 1;
    
    $stmt = $conn->prepare("
        SELECT 
            tb.*,
            t1.team_name as team1_name,
            t2.team_name as team2_name,
            tw.team_name as winner_name,
            r.room_code,
            r.team1_score,
            r.team2_score
        FROM tournament_bracket tb
        JOIN teams t1 ON tb.team1_id = t1.id
        JOIN teams t2 ON tb.team2_id = t2.id
        LEFT JOIN teams tw ON tb.winner_team_id = tw.id
        LEFT JOIN rooms r ON tb.room_id = r.id
        WHERE tb.wave_id = ?
        ORDER BY tb.match_number
    ");
    $stmt->bind_param("i", $wave_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $matches = [];
    while ($row = $result->fetch_assoc()) {
        $matches[] = $row;
    }
    
    sendJSON(['success' => true, 'matches' => $matches]);
}

function addQuestions($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $wave_id = $data['wave_id'] ?? 0;
    $questions = $data['questions'] ?? [];
    
    if (empty($questions)) {
        handleError("No questions provided");
    }
    
    $conn->begin_transaction();
    
    try {
        // Delete existing questions for this wave
        $stmt = $conn->prepare("DELETE FROM questions WHERE wave_id = ?");
        $stmt->bind_param("i", $wave_id);
        $stmt->execute();
        
        // Insert new questions
        $stmt = $conn->prepare("
            INSERT INTO questions 
            (wave_id, question_number, question_image_url, option_a, option_b, option_c, option_d, correct_answer)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($questions as $q) {
            $stmt->bind_param(
                "iissssss",
                $wave_id,
                $q['question_number'],
                $q['image_url'],
                $q['option_a'],
                $q['option_b'],
                $q['option_c'],
                $q['option_d'],
                $q['correct_answer']
            );
            $stmt->execute();
        }
        
        $conn->commit();
        sendJSON(['success' => true, 'message' => 'Questions added successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        handleError("Failed to add questions: " . $e->getMessage());
    }
}

function getWaves($conn) {
    $stmt = $conn->prepare("SELECT * FROM waves ORDER BY wave_number");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $waves = [];
    while ($row = $result->fetch_assoc()) {
        $waves[] = $row;
    }
    
    sendJSON(['success' => true, 'waves' => $waves]);
}

function updateBracket($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $current_wave = $data['current_wave'] ?? 1;
    
    // Get winners from current wave
    $stmt = $conn->prepare("
        SELECT winner_team_id 
        FROM tournament_bracket 
        WHERE wave_id = ? AND winner_team_id IS NOT NULL
        ORDER BY match_number
    ");
    $stmt->bind_param("i", $current_wave);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $winners = [];
    while ($row = $result->fetch_assoc()) {
        $winners[] = $row['winner_team_id'];
    }
    
    if (count($winners) < 2) {
        sendJSON(['success' => false, 'message' => 'Not enough winners to create next round']);
        return;
    }
    
    $next_wave = $current_wave + 1;
    
    // Check if next wave bracket already exists
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tournament_bracket WHERE wave_id = ?");
    $stmt->bind_param("i", $next_wave);
    $stmt->execute();
    $count_result = $stmt->get_result()->fetch_assoc();
    
    if ($count_result['count'] > 0) {
        sendJSON(['success' => true, 'message' => 'Next round bracket already exists']);
        return;
    }
    
    // Create next round matches
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO tournament_bracket (wave_id, match_number, team1_id, team2_id)
            VALUES (?, ?, ?, ?)
        ");
        
        $match_number = 1;
        for ($i = 0; $i < count($winners); $i += 2) {
            if (isset($winners[$i + 1])) {
                $stmt->bind_param("iiii", $next_wave, $match_number, $winners[$i], $winners[$i + 1]);
                $stmt->execute();
                $match_number++;
            }
        }
        
        $conn->commit();
        sendJSON(['success' => true, 'message' => 'Next round bracket created']);
    } catch (Exception $e) {
        $conn->rollback();
        handleError("Failed to create next round: " . $e->getMessage());
    }
}

$conn->close();
?>