<?php
// debug_ambassador_system.php - Complete diagnostic for ambassador referral system
define('SECURE_ACCESS', true);

require_once '../config.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", 
                   DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "<h1>Ambassador Referral System Diagnostic</h1>";
    echo "<style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
    </style>";

    // 1. Check ambassadors table
    echo "<div class='section'>";
    echo "<h2>1. Ambassadors in Database</h2>";
    
    $ambassadors = $pdo->query("SELECT * FROM ambassadors ORDER BY ambassador_id")->fetchAll();
    
    if (empty($ambassadors)) {
        echo "<p class='error'>❌ No ambassadors found in database!</p>";
    } else {
        echo "<p class='success'>✅ Found " . count($ambassadors) . " ambassadors</p>";
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Referral Count</th><th>Registration Date</th></tr>";
        
        foreach ($ambassadors as $amb) {
            $statusClass = $amb['status'] === 'active' ? 'success' : 'error';
            echo "<tr>";
            echo "<td>{$amb['ambassador_id']}</td>";
            echo "<td><strong>{$amb['name']}</strong></td>";
            echo "<td>{$amb['email']}</td>";
            echo "<td class='{$statusClass}'>{$amb['status']}</td>";
            echo "<td><strong>{$amb['referral_count']}</strong></td>";
            echo "<td>{$amb['registration_date']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        $activeCount = count(array_filter($ambassadors, function($a) { return $a['status'] === 'active'; }));
        echo "<p>Active ambassadors: <span class='success'>{$activeCount}</span></p>";
    }
    echo "</div>";

    // 2. Check teams with referrals
    echo "<div class='section'>";
    echo "<h2>2. Teams with Ambassador Referrals</h2>";
    
    $teamsWithReferrals = $pdo->query("
        SELECT t.id, t.team_name, t.school_name, t.referred_by_ambassador, a.name as ambassador_name, t.registration_date
        FROM teams t 
        LEFT JOIN ambassadors a ON t.referred_by_ambassador = a.ambassador_id 
        WHERE t.referred_by_ambassador IS NOT NULL
        ORDER BY t.registration_date DESC
        LIMIT 20
    ")->fetchAll();
    
    if (empty($teamsWithReferrals)) {
        echo "<p class='warning'>⚠️ No teams with ambassador referrals found</p>";
    } else {
        echo "<p class='success'>✅ Found " . count($teamsWithReferrals) . " teams with referrals</p>";
        
        echo "<table>";
        echo "<tr><th>Team ID</th><th>Team Name</th><th>School</th><th>Ambassador ID</th><th>Ambassador Name</th><th>Registration Date</th></tr>";
        
        foreach ($teamsWithReferrals as $team) {
            $ambassadorClass = $team['ambassador_name'] ? 'success' : 'error';
            echo "<tr>";
            echo "<td>{$team['id']}</td>";
            echo "<td>{$team['team_name']}</td>";
            echo "<td>{$team['school_name']}</td>";
            echo "<td>{$team['referred_by_ambassador']}</td>";
            echo "<td class='{$ambassadorClass}'>" . ($team['ambassador_name'] ?: 'NOT FOUND') . "</td>";
            echo "<td>{$team['registration_date']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";

    // 3. Test referral count calculation
    echo "<div class='section'>";
    echo "<h2>3. Referral Count Verification</h2>";
    
    echo "<p>Comparing recorded referral_count vs actual team count:</p>";
    
    $referralStats = $pdo->query("
        SELECT 
            a.ambassador_id,
            a.name,
            a.referral_count as recorded_count,
            COUNT(t.id) as actual_count
        FROM ambassadors a
        LEFT JOIN teams t ON a.ambassador_id = t.referred_by_ambassador
        GROUP BY a.ambassador_id
        ORDER BY actual_count DESC, a.name ASC
    ")->fetchAll();
    
    echo "<table>";
    echo "<tr><th>Ambassador ID</th><th>Name</th><th>Recorded Count</th><th>Actual Count</th><th>Status</th></tr>";
    
    foreach ($referralStats as $stat) {
        $match = $stat['recorded_count'] == $stat['actual_count'];
        $statusClass = $match ? 'success' : 'error';
        $statusText = $match ? '✅ Match' : '❌ Mismatch';
        
        echo "<tr>";
        echo "<td>{$stat['ambassador_id']}</td>";
        echo "<td>{$stat['name']}</td>";
        echo "<td>{$stat['recorded_count']}</td>";
        echo "<td>{$stat['actual_count']}</td>";
        echo "<td class='{$statusClass}'>{$statusText}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";

    // 4. Test ambassador lookup
    if (!empty($ambassadors)) {
        echo "<div class='section'>";
        echo "<h2>4. Test Ambassador Lookup</h2>";
        
        $testAmbassador = $ambassadors[0]; // Use first ambassador
        $testId = $testAmbassador['ambassador_id'];
        $testName = $testAmbassador['name'];
        
        echo "<p>Testing lookup for: <strong>{$testName}</strong> (ID: {$testId})</p>";
        
        // Test by ID
        $lookupById = $pdo->prepare("SELECT ambassador_id, name FROM ambassadors WHERE ambassador_id = ? AND status = 'active'");
        $lookupById->execute([$testId]);
        $foundById = $lookupById->fetch();
        
        echo "<p>Lookup by ID ({$testId}): ";
        if ($foundById) {
            echo "<span class='success'>✅ Found: {$foundById['name']}</span>";
        } else {
            echo "<span class='error'>❌ Not found</span>";
        }
        echo "</p>";
        
        // Test by name
        $lookupByName = $pdo->prepare("SELECT ambassador_id, name FROM ambassadors WHERE name = ? AND status = 'active'");
        $lookupByName->execute([$testName]);
        $foundByName = $lookupByName->fetch();
        
        echo "<p>Lookup by name ('{$testName}'): ";
        if ($foundByName) {
            echo "<span class='success'>✅ Found: ID {$foundByName['ambassador_id']}</span>";
        } else {
            echo "<span class='error'>❌ Not found</span>";
        }
        echo "</p>";
        
        echo "</div>";
    }

    // 5. Test manual referral count update
    if (!empty($ambassadors)) {
        $testAmbassador = $ambassadors[0];
        $testId = $testAmbassador['ambassador_id'];
        $currentCount = $testAmbassador['referral_count'];
        
        echo "<div class='section'>";
        echo "<h2>5. Test Manual Referral Count Update</h2>";
        echo "<p><strong>WARNING:</strong> This will temporarily modify the database</p>";
        
        echo "<p>Testing with: <strong>{$testAmbassador['name']}</strong></p>";
        echo "<p>Current count: <strong>{$currentCount}</strong></p>";
        
        // Test increment
        $testNewCount = $currentCount + 1;
        $updateStmt = $pdo->prepare("UPDATE ambassadors SET referral_count = ? WHERE ambassador_id = ?");
        $updateResult = $updateStmt->execute([$testNewCount, $testId]);
        $rowsAffected = $updateStmt->rowCount();
        
        // Verify
        $verifyStmt = $pdo->prepare("SELECT referral_count FROM ambassadors WHERE ambassador_id = ?");
        $verifyStmt->execute([$testId]);
        $verifiedCount = $verifyStmt->fetchColumn();
        
        echo "<p>Update result: " . ($updateResult ? 'SUCCESS' : 'FAILED') . "</p>";
        echo "<p>Rows affected: {$rowsAffected}</p>";
        echo "<p>Verified count: <strong>{$verifiedCount}</strong></p>";
        echo "<p>Expected: <strong>{$testNewCount}</strong></p>";
        
        if ($verifiedCount == $testNewCount) {
            echo "<p class='success'>✅ Manual update works correctly!</p>";
        } else {
            echo "<p class='error'>❌ Manual update failed!</p>";
        }
        
        // Reset to original
        $resetStmt = $pdo->prepare("UPDATE ambassadors SET referral_count = ? WHERE ambassador_id = ?");
        $resetStmt->execute([$currentCount, $testId]);
        echo "<p><em>Count reset to original value: {$currentCount}</em></p>";
        
        echo "</div>";
    }

    // 6. Check recent registrations
    echo "<div class='section'>";
    echo "<h2>6. Recent Team Registrations</h2>";
    
    $recentTeams = $pdo->query("
        SELECT id, team_name, school_name, referred_by_ambassador, registration_date
        FROM teams 
        ORDER BY registration_date DESC 
        LIMIT 10
    ")->fetchAll();
    
    if (empty($recentTeams)) {
        echo "<p class='warning'>⚠️ No teams registered yet</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Team Name</th><th>School</th><th>Ambassador ID</th><th>Registration Date</th></tr>";
        
        foreach ($recentTeams as $team) {
            $refClass = $team['referred_by_ambassador'] ? 'success' : 'warning';
            echo "<tr>";
            echo "<td>{$team['id']}</td>";
            echo "<td>{$team['team_name']}</td>";
            echo "<td>{$team['school_name']}</td>";
            echo "<td class='{$refClass}'>" . ($team['referred_by_ambassador'] ?: 'None') . "</td>";
            echo "<td>{$team['registration_date']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";

} catch (Exception $e) {
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<p><a href="javascript:history.back()">← Back</a></p>