<?php
// api.php - Backend API handler
session_start();
require_once '../config1.php'; // Load config from outside public_html

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $pdo = getDBConnection();
    
    switch ($action) {
        case 'admin_login':
            adminLogin($pdo);
            break;
        case 'admin_logout':
            adminLogout();
            break;
        case 'add_team':
            requireAdmin();
            addTeam($pdo);
            break;
        case 'delete_team':
            requireAdmin();
            deleteTeam($pdo);
            break;
        case 'get_teams':
            getTeams($pdo);
            break;
        case 'save_question_answers':
            requireAdmin();
            saveQuestionAnswers($pdo);
            break;
        case 'get_questions':
            getQuestions($pdo);
            break;
        case 'team_login':
            teamLogin($pdo);
            break;
        case 'team_logout':
            teamLogout();
            break;
        case 'submit_answer':
            requireTeamLogin();
            submitAnswer($pdo);
            break;
        case 'submit_file':
            requireTeamLogin();
            submitFile($pdo);
            break;
        case 'get_team_status':
            requireTeamLogin();
            getTeamStatus($pdo);
            break;
        case 'get_leaderboard':
            getLeaderboard($pdo);
            break;
        case 'get_pending_submissions':
            requireAdmin();
            getPendingSubmissions($pdo);
            break;
        case 'review_submission':
            requireAdmin();
            reviewSubmission($pdo);
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Helper Functions
function requireAdmin() {
    if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        throw new Exception('Admin access required');
    }
}

function requireTeamLogin() {
    if (!isset($_SESSION['team_id'])) {
        throw new Exception('Team login required');
    }
}

// Admin Functions
function adminLogin($pdo) {
    $password = $_POST['password'] ?? '';
    
    if (verifyAdmin($password)) {
        $_SESSION['is_admin'] = true;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect password']);
    }
}

function adminLogout() {
    unset($_SESSION['is_admin']);
    echo json_encode(['success' => true]);
}

function addTeam($pdo) {
    $teamName = sanitize($_POST['team_name'] ?? '');
    $passcode = $_POST['passcode'] ?? '';
    
    if (empty($teamName) || empty($passcode)) {
        throw new Exception('Team name and passcode required');
    }
    
    // Hash the passcode
    $hashedPasscode = password_hash($passcode, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO teams (team_name, passcode) VALUES (?, ?)");
    $stmt->execute([$teamName, $hashedPasscode]);
    
    $teamId = $pdo->lastInsertId();
    
    // Initialize team_question_status for all 30 questions
    $stmt = $pdo->prepare("INSERT INTO team_question_status (team_id, question_number) VALUES (?, ?)");
    for ($i = 1; $i <= 30; $i++) {
        $stmt->execute([$teamId, $i]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Team added successfully']);
}

function deleteTeam($pdo) {
    $teamId = intval($_POST['team_id'] ?? 0);
    
    if ($teamId <= 0) {
        throw new Exception('Invalid team ID');
    }
    
    $stmt = $pdo->prepare("DELETE FROM teams WHERE id = ?");
    $stmt->execute([$teamId]);
    
    echo json_encode(['success' => true, 'message' => 'Team deleted successfully']);
}

function getTeams($pdo) {
    $stmt = $pdo->query("SELECT id, team_name, score, created_at FROM teams ORDER BY team_name");
    $teams = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'teams' => $teams]);
}

function saveQuestionAnswers($pdo) {
    $questions = json_decode($_POST['questions'] ?? '{}', true);
    
    $stmt = $pdo->prepare("UPDATE questions SET correct_answer = ?, acceptable_range = ? WHERE question_number = ?");
    
    foreach ($questions as $qNum => $data) {
        $answer = $data['answer'] !== '' ? floatval($data['answer']) : null;
        $range = floatval($data['range'] ?? 0);
        $stmt->execute([$answer, $range, $qNum]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Answers saved successfully']);
}

function getQuestions($pdo) {
    $stmt = $pdo->query("SELECT * FROM questions ORDER BY question_number");
    $questions = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'questions' => $questions]);
}

// Team Functions
function teamLogin($pdo) {
    $teamName = sanitize($_POST['team_name'] ?? '');
    $passcode = $_POST['passcode'] ?? '';
    
    if (empty($teamName) || empty($passcode)) {
        throw new Exception('Team name and passcode required');
    }
    
    $stmt = $pdo->prepare("SELECT id, team_name, passcode, score FROM teams WHERE team_name = ?");
    $stmt->execute([$teamName]);
    $team = $stmt->fetch();
    
    if ($team && password_verify($passcode, $team['passcode'])) {
        $_SESSION['team_id'] = $team['id'];
        $_SESSION['team_name'] = $team['team_name'];
        echo json_encode(['success' => true, 'team' => [
            'id' => $team['id'],
            'name' => $team['team_name'],
            'score' => $team['score']
        ]]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
}

function teamLogout() {
    unset($_SESSION['team_id']);
    unset($_SESSION['team_name']);
    echo json_encode(['success' => true]);
}

function submitAnswer($pdo) {
    $teamId = $_SESSION['team_id'];
    $questionNum = intval($_POST['question_number'] ?? 0);
    $answer = floatval($_POST['answer'] ?? 0);
    
    if ($questionNum < 1 || $questionNum > 25) {
        throw new Exception('Invalid question number for numerical answer');
    }
    
    // Check current status
    $stmt = $pdo->prepare("SELECT attempts_used, is_solved FROM team_question_status WHERE team_id = ? AND question_number = ?");
    $stmt->execute([$teamId, $questionNum]);
    $status = $stmt->fetch();
    
    if ($status['is_solved']) {
        throw new Exception('Question already solved');
    }
    
    if ($status['attempts_used'] >= 3) {
        throw new Exception('No attempts remaining');
    }
    
    $attemptNum = $status['attempts_used'] + 1;
    
    // Get question details
    $stmt = $pdo->prepare("SELECT correct_answer, acceptable_range, points_first_attempt, points_second_attempt, points_third_attempt FROM questions WHERE question_number = ?");
    $stmt->execute([$questionNum]);
    $question = $stmt->fetch();
    
    if (!$question || $question['correct_answer'] === null) {
        throw new Exception('Question answer not set by admin');
    }
    
    // Check if answer is correct
    $isCorrect = abs($answer - $question['correct_answer']) <= $question['acceptable_range'];
    
    $pointsEarned = 0;
    if ($isCorrect) {
        switch ($attemptNum) {
            case 1: $pointsEarned = $question['points_first_attempt']; break;
            case 2: $pointsEarned = $question['points_second_attempt']; break;
            case 3: $pointsEarned = $question['points_third_attempt']; break;
        }
    }
    
    // Record submission
    $stmt = $pdo->prepare("INSERT INTO submissions (team_id, question_number, attempt_number, answer_value, is_correct, points_earned) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$teamId, $questionNum, $attemptNum, $answer, $isCorrect, $pointsEarned]);
    
    // Update team_question_status
    $stmt = $pdo->prepare("UPDATE team_question_status SET attempts_used = ?, is_solved = ?, last_attempt_at = NOW() WHERE team_id = ? AND question_number = ?");
    $stmt->execute([$attemptNum, $isCorrect, $teamId, $questionNum]);
    
    // Update team score if correct
    if ($isCorrect) {
        $stmt = $pdo->prepare("UPDATE teams SET score = score + ? WHERE id = ?");
        $stmt->execute([$pointsEarned, $teamId]);
    }
    
    echo json_encode([
        'success' => true,
        'correct' => $isCorrect,
        'points_earned' => $pointsEarned,
        'attempts_left' => 3 - $attemptNum,
        'message' => $isCorrect ? "Correct! You earned $pointsEarned points!" : "Incorrect. " . (3 - $attemptNum) . " attempts remaining."
    ]);
}

function submitFile($pdo) {
    $teamId = $_SESSION['team_id'];
    $questionNum = intval($_POST['question_number'] ?? 0);
    
    if ($questionNum < 26 || $questionNum > 30) {
        throw new Exception('Invalid question number for file upload');
    }
    
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error');
    }
    
    // Check current status
    $stmt = $pdo->prepare("SELECT attempts_used, is_solved FROM team_question_status WHERE team_id = ? AND question_number = ?");
    $stmt->execute([$teamId, $questionNum]);
    $status = $stmt->fetch();
    
    if ($status['is_solved']) {
        throw new Exception('Question already solved');
    }
    
    if ($status['attempts_used'] >= 3) {
        throw new Exception('No attempts remaining');
    }
    
    $file = $_FILES['file'];
    
    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('File too large (max 10MB)');
    }
    
    // Validate file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        throw new Exception('Invalid file type. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS));
    }
    
    // Generate unique filename
    $newFileName = $teamId . '_q' . $questionNum . '_' . time() . '.' . $ext;
    $uploadPath = UPLOAD_DIR . $newFileName;
    
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('Failed to save file');
    }
    
    $attemptNum = $status['attempts_used'] + 1;
    
    // Record submission
    $stmt = $pdo->prepare("INSERT INTO submissions (team_id, question_number, attempt_number, file_name, file_path, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$teamId, $questionNum, $attemptNum, $file['name'], $uploadPath]);
    
    // Update team_question_status
    $stmt = $pdo->prepare("UPDATE team_question_status SET attempts_used = ?, last_attempt_at = NOW() WHERE team_id = ? AND question_number = ?");
    $stmt->execute([$attemptNum, $teamId, $questionNum]);
    
    echo json_encode([
        'success' => true,
        'message' => 'File submitted successfully! Waiting for admin review.'
    ]);
}

function getTeamStatus($pdo) {
    $teamId = $_SESSION['team_id'];
    
    // Get team info
    $stmt = $pdo->prepare("SELECT team_name, score FROM teams WHERE id = ?");
    $stmt->execute([$teamId]);
    $team = $stmt->fetch();
    
    // Get all question statuses
    $stmt = $pdo->prepare("SELECT question_number, attempts_used, is_solved FROM team_question_status WHERE team_id = ? ORDER BY question_number");
    $stmt->execute([$teamId]);
    $statuses = $stmt->fetchAll(PDO::FETCH_GROUP);
    
    // Get pending file submissions
    $stmt = $pdo->prepare("SELECT question_number FROM submissions WHERE team_id = ? AND status = 'pending' AND question_number >= 26");
    $stmt->execute([$teamId]);
    $pending = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'team' => $team,
        'statuses' => $statuses,
        'pending' => $pending
    ]);
}

function getLeaderboard($pdo) {
    $stmt = $pdo->query("
        SELECT 
            t.team_name,
            t.score,
            COUNT(CASE WHEN tqs.is_solved = 1 THEN 1 END) as solved_count
        FROM teams t
        LEFT JOIN team_question_status tqs ON t.id = tqs.team_id
        GROUP BY t.id, t.team_name, t.score
        ORDER BY t.score DESC, t.team_name ASC
    ");
    $leaderboard = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'leaderboard' => $leaderboard]);
}

function getPendingSubmissions($pdo) {
    $stmt = $pdo->query("
        SELECT 
            s.id,
            s.team_id,
            s.question_number,
            s.file_name,
            s.submitted_at,
            t.team_name
        FROM submissions s
        JOIN teams t ON s.team_id = t.id
        WHERE s.status = 'pending' AND s.question_number >= 26
        ORDER BY s.submitted_at ASC
    ");
    $submissions = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'submissions' => $submissions]);
}

function reviewSubmission($pdo) {
    $submissionId = intval($_POST['submission_id'] ?? 0);
    $approved = $_POST['approved'] === 'true';
    
    if ($submissionId <= 0) {
        throw new Exception('Invalid submission ID');
    }
    
    // Get submission details
    $stmt = $pdo->prepare("SELECT team_id, question_number FROM submissions WHERE id = ?");
    $stmt->execute([$submissionId]);
    $submission = $stmt->fetch();
    
    if (!$submission) {
        throw new Exception('Submission not found');
    }
    
    if ($approved) {
        // Mark as approved and award points
        $pointsEarned = 10; // File questions always worth 10 points
        
        $stmt = $pdo->prepare("UPDATE submissions SET status = 'approved', points_earned = ?, reviewed_at = NOW() WHERE id = ?");
        $stmt->execute([$pointsEarned, $submissionId]);
        
        // Mark question as solved
        $stmt = $pdo->prepare("UPDATE team_question_status SET is_solved = 1 WHERE team_id = ? AND question_number = ?");
        $stmt->execute([$submission['team_id'], $submission['question_number']]);
        
        // Update team score
        $stmt = $pdo->prepare("UPDATE teams SET score = score + ? WHERE id = ?");
        $stmt->execute([$pointsEarned, $submission['team_id']]);
        
        $message = 'Submission approved! 10 points awarded.';
    } else {
        // Mark as rejected
        $stmt = $pdo->prepare("UPDATE submissions SET status = 'rejected', reviewed_at = NOW() WHERE id = ?");
        $stmt->execute([$submissionId]);
        
        $message = 'Submission rejected.';
    }
    
    echo json_encode(['success' => true, 'message' => $message]);
}
?>