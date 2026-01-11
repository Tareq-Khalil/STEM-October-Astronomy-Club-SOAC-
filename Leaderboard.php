<?php
// ambassador_leaderboard.php - Public leaderboard for ambassadors
define('SECURE_ACCESS', true);
require_once '../config.php';

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", 
                   DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Get all active ambassadors
    $ambassadorStmt = $pdo->query("SELECT ambassador_id, name, school_name, registration_date FROM ambassadors WHERE status = 'active' ORDER BY name ASC");
    $ambassadors = $ambassadorStmt->fetchAll();
    
    // Get actual referral counts from teams table
    $actualReferrals = [];
    $totalReferrals = 0;
    try {
        // Check if teams table exists and has the right structure
        $checkTable = $pdo->query("SHOW TABLES LIKE 'teams'");
        if ($checkTable->rowCount() > 0) {
            $checkColumns = $pdo->query("SHOW COLUMNS FROM teams LIKE 'referred_by_ambassador'");
            if ($checkColumns->rowCount() > 0) {
                $referralStmt = $pdo->query("
                    SELECT referred_by_ambassador, COUNT(*) as count 
                    FROM teams 
                    WHERE referred_by_ambassador IS NOT NULL 
                    GROUP BY referred_by_ambassador
                ");
                while ($row = $referralStmt->fetch()) {
                    $actualReferrals[$row['referred_by_ambassador']] = $row['count'];
                    $totalReferrals += $row['count'];
                }
            }
        }
    } catch (Exception $e) {
        error_log("Teams table issue: " . $e->getMessage());
    }
    
    // Add referral counts to ambassador data and filter out those with 0 referrals for ranking
    $leaderboardData = [];
    foreach ($ambassadors as $ambassador) {
        $referralCount = $actualReferrals[$ambassador['ambassador_id']] ?? 0;
        $ambassador['referral_count'] = $referralCount;
        $leaderboardData[] = $ambassador;
    }
    
    // Sort by referral count (descending) then by name (ascending)
    usort($leaderboardData, function($a, $b) {
        if ($b['referral_count'] == $a['referral_count']) {
            return strcmp($a['name'], $b['name']);
        }
        return $b['referral_count'] - $a['referral_count'];
    });
    
    // Get stats
    $totalAmbassadors = count($ambassadors);
    $activeAmbassadors = count(array_filter($leaderboardData, function($a) { return $a['referral_count'] > 0; }));
    
} catch (PDOException $e) {
    die("Database error: Unable to load leaderboard data.");
}

// Get current date for last updated
$lastUpdated = date('F j, Y \a\t g:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambassador Leaderboard - Cosmic Quest</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 3rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            display: block;
        }
        
        .stat-label {
            color: #666;
            font-size: 1rem;
            margin-top: 8px;
        }
        
        .leaderboard {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .leaderboard-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px;
            text-align: center;
        }
        
        .leaderboard-header h2 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .leaderboard-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .podium {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            padding: 30px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }
        
        .podium-item {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border: 3px solid;
            position: relative;
            overflow: hidden;
        }
        
        .podium-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, transparent, currentColor, transparent);
        }
        
        .podium-first {
            border-color: #ffd700;
            color: #ffd700;
        }
        
        .podium-second {
            border-color: #c0c0c0;
            color: #c0c0c0;
        }
        
        .podium-third {
            border-color: #cd7f32;
            color: #cd7f32;
        }
        
        .rank-badge {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .podium-name {
            font-size: 1.4rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        
        .podium-school {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .podium-referrals {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .podium-label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .full-leaderboard {
            padding: 0;
        }
        
        .leaderboard-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .leaderboard-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }
        
        .leaderboard-table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .leaderboard-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .rank-cell {
            font-weight: bold;
            color: #667eea;
            width: 80px;
            text-align: center;
        }
        
        .name-cell {
            font-weight: 600;
            color: #333;
        }
        
        .school-cell {
            color: #666;
            font-size: 0.9rem;
        }
        
        .referrals-cell {
            font-weight: bold;
            color: #667eea;
            text-align: center;
            font-size: 1.1rem;
        }
        
        .zero-referrals {
            color: #999;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            color: white;
            opacity: 0.8;
        }
        
        .last-updated {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .prize-info {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #333;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .prize-info h3 {
            margin-bottom: 10px;
            font-size: 1.3rem;
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .header p {
                font-size: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .podium {
                grid-template-columns: 1fr;
                padding: 20px;
            }
            
            .leaderboard-table {
                font-size: 14px;
            }
            
            .leaderboard-table th,
            .leaderboard-table td {
                padding: 10px 8px;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🏆 Ambassador Leaderboard</h1>
            <p>See how our amazing ambassadors are spreading the word about Cosmic Quest!</p>
        </header>
        
        <div class="prize-info">
            <h3>🎁 Grand Prize</h3>
            <p>The top 3 ambassador each wins <strong>AoPS coupon worth $25</strong>!</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number"><?php echo $totalAmbassadors; ?></span>
                <div class="stat-label">Total Ambassadors</div>
            </div>
        </div>
        
        <div class="leaderboard">
            <div class="leaderboard-header">
                <h2>🥇 Top Performers</h2>
                <p>Leading ambassadors making the biggest impact</p>
            </div>
            
            <?php
            // Get top 3 for podium
            $topThree = array_slice(array_filter($leaderboardData, function($a) { return $a['referral_count'] > 0; }), 0, 3);
            ?>
            
            <?php if (!empty($topThree)): ?>
            <div class="podium">
                <?php foreach ($topThree as $index => $ambassador): ?>
                <div class="podium-item <?php echo ['podium-first', 'podium-second', 'podium-third'][$index]; ?>">
                    <div class="rank-badge">
                        <?php echo ['🥇', '🥈', '🥉'][$index]; ?>
                    </div>
                    <div class="podium-name"><?php echo htmlspecialchars($ambassador['name']); ?></div>
                    <?php if ($ambassador['school_name']): ?>
                    <div class="podium-school"><?php echo htmlspecialchars($ambassador['school_name']); ?></div>
                    <?php endif; ?>
                    <div class="podium-referrals"><?php echo $ambassador['referral_count']; ?></div>
                    <div class="podium-label">Referrals</div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="full-leaderboard">
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Name</th>
                            <th>School</th>
                            <th>Referrals</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leaderboardData)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 40px; color: #666;">
                                No ambassadors registered yet. Be the first to join!
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($leaderboardData as $index => $ambassador): ?>
                        <tr>
                            <td class="rank-cell">
                                <?php if ($index === 0 && $ambassador['referral_count'] > 0): ?>
                                    🥇 #1
                                <?php elseif ($index === 1 && $ambassador['referral_count'] > 0): ?>
                                    🥈 #2
                                <?php elseif ($index === 2 && $ambassador['referral_count'] > 0): ?>
                                    🥉 #3
                                <?php else: ?>
                                    #<?php echo $index + 1; ?>
                                <?php endif; ?>
                            </td>
                            <td class="name-cell">
                                <?php echo htmlspecialchars($ambassador['name']); ?>
                            </td>
                            <td class="school-cell">
                                <?php echo htmlspecialchars($ambassador['school_name'] ?: 'Not specified'); ?>
                            </td>
                            <td class="referrals-cell <?php echo $ambassador['referral_count'] == 0 ? 'zero-referrals' : ''; ?>">
                                <?php echo $ambassador['referral_count']; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <footer class="footer">
            <div class="last-updated">
                <strong>Last Updated:</strong> <?php echo $lastUpdated; ?>
                <br><br>
                <p>Want to become an ambassador? You still have a chance, <a href="ambassador.html">apply now</a>!</p>
            </div>
        </footer>
    </div>
    
    <script>
        // Auto-refresh every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>