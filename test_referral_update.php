<?php
// test_referral_update.php - Test script to debug referral count updates
define('SECURE_ACCESS', true);

require_once '../config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", 
                   DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "<h2>Ambassador Referral Count Test</h2>";
    
    // Show all ambassadors
    echo "<h3>All Ambassadors:</h3>";
    $ambassadors = $pdo->query("SELECT * FROM ambassadors ORDER BY ambassador_id")->fetchAll();
    
    if (empty($ambassadors)) {
        echo "<p style='color: red;'>No ambassadors found in database!</p>";
        exit;
    }
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Referral Count</th><th>Status</th></tr>";
    
    foreach ($ambassadors as $amb) {
        echo "<tr>";
        echo "<td>{$amb['ambassador_id']}</td>";
        echo "<td>{$amb['name']}</td>";
        echo "<td>{$amb['email']}</td>";
        echo "<td><strong>{$amb['referral_count']}</strong></td>";
        echo "<td>{$amb['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test updating the first active ambassador
    $activeAmbassadors = array_filter($ambassadors, function($a) { return $a['status'] === 'active'; });
    
    if (empty($activeAmbassadors)) {
        echo "<p style='color: red;'>No active ambassadors found!</p>";
        exit;
    }
    
    $testAmbassador = reset($activeAmbassadors);
    $testId = $testAmbassador['ambassador_id'];
    $currentCount = $testAmbassador['referral_count'];
    
    echo "<h3>Testing Referral Count Update</h3>";
    echo "<p>Testing with Ambassador: <strong>{$testAmbassador['name']}</strong> (ID: {$testId})</p>";
    echo "<p>Current referral count: <strong>{$currentCount}</strong></p>";
    
    // Method 1: INCREMENT
    echo "<h4>Method 1: Using INCREMENT (referral_count = referral_count + 1)</h4>";
    $stmt1 = $pdo->prepare("UPDATE ambassadors SET referral_count = referral_count + 1 WHERE ambassador_id = ?");
    $result1 = $stmt1->execute([$testId]);
    $affected1 = $stmt1->rowCount();
    
    // Check result
    $checkStmt = $pdo->prepare("SELECT referral_count FROM ambassadors WHERE ambassador_id = ?");
    $checkStmt->execute([$testId]);
    $newCount1 = $checkStmt->fetchColumn();
    
    echo "<p>Result: " . ($result1 ? 'SUCCESS' : 'FAILED') . "</p>";
    echo "<p>Rows affected: {$affected1}</p>";
    echo "<p>New count: <strong>{$newCount1}</strong></p>";
    echo "<p>Expected: <strong>" . ($currentCount + 1) . "</strong></p>";
    
    if ($newCount1 == ($currentCount + 1)) {
        echo "<p style='color: green;'>✓ Method 1 works correctly!</p>";
    } else {
        echo "<p style='color: red;'>✗ Method 1 failed!</p>";
    }
    
    // Reset to original count for next test
    $pdo->prepare("UPDATE ambassadors SET referral_count = ? WHERE ambassador_id = ?")->execute([$currentCount, $testId]);
    
    // Method 2: EXPLICIT VALUE
    echo "<h4>Method 2: Using EXPLICIT VALUE (referral_count = ?)</h4>";
    $newExplicitCount = $currentCount + 1;
    $stmt2 = $pdo->prepare("UPDATE ambassadors SET referral_count = ? WHERE ambassador_id = ?");
    $result2 = $stmt2->execute([$newExplicitCount, $testId]);
    $affected2 = $stmt2->rowCount();
    
    // Check result
    $checkStmt->execute([$testId]);
    $newCount2 = $checkStmt->fetchColumn();
    
    echo "<p>Result: " . ($result2 ? 'SUCCESS' : 'FAILED') . "</p>";
    echo "<p>Rows affected: {$affected2}</p>";
    echo "<p>New count: <strong>{$newCount2}</strong></p>";
    echo "<p>Expected: <strong>{$newExplicitCount}</strong></p>";
    
    if ($newCount2 == $newExplicitCount) {
        echo "<p style='color: green;'>✓ Method 2 works correctly!</p>";
    } else {
        echo "<p style='color: red;'>✗ Method 2 failed!</p>";
    }
    
    // Reset to original count
    $pdo->prepare("UPDATE ambassadors SET referral_count = ? WHERE ambassador_id = ?")->execute([$currentCount, $testId]);
    
    echo "<p><em>Referral count has been reset to original value: {$currentCount}</em></p>";
    
    // Show teams with referrals
    echo "<h3>Teams with Ambassador Referrals:</h3>";
    $teamsWithReferrals = $pdo->query("
        SELECT t.id, t.team_name, t.referred_by_ambassador, a.name as ambassador_name 
        FROM teams t 
        LEFT JOIN ambassadors a ON t.referred_by_ambassador = a.ambassador_id 
        WHERE t.referred_by_ambassador IS NOT NULL
        ORDER BY t.id DESC
        LIMIT 10
    ")->fetchAll();
    
    if (empty($teamsWithReferrals)) {
        echo "<p>No teams with ambassador referrals found.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Team ID</th><th>Team Name</th><th>Ambassador ID</th><th>Ambassador Name</th></tr>";
        
        foreach ($teamsWithReferrals as $team) {
            echo "<tr>";
            echo "<td>{$team['id']}</td>";
            echo "<td>{$team['team_name']}</td>";
            echo "<td>{$team['referred_by_ambassador']}</td>";
            echo "<td>{$team['ambassador_name']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<p><a href="javascript:history.back()">← Back</a></p>