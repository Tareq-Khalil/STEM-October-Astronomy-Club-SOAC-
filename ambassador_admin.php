<?php
// ambassador_admin.php - Admin panel for managing ambassadors
define('SECURE_ACCESS', true);

require_once '../config.php';

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    if (isset($_POST['password'])) {
        if ($_POST['password'] === ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;
        } else {
            $error = "Incorrect password";
        }
    }
    
    if (!isset($_SESSION['admin_logged_in'])) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Ambassador Admin - Cosmic Quest</title>
            <style>
                body { 
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                    max-width: 400px; 
                    margin: 100px auto; 
                    padding: 20px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .login-container {
                    background: white;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                    width: 100%;
                }
                h2 { text-align: center; color: #333; margin-bottom: 30px; }
                input[type="password"], input[type="submit"] { 
                    width: 100%; 
                    padding: 12px; 
                    margin: 10px 0; 
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    font-size: 16px;
                    box-sizing: border-box;
                }
                input[type="submit"] {
                    background: #667eea;
                    color: white;
                    border: none;
                    cursor: pointer;
                    font-weight: bold;
                }
                input[type="submit"]:hover {
                    background: #5a67d8;
                }
                .error { color: red; text-align: center; margin-bottom: 15px; }
            </style>
        </head>
        <body>
            <div class="login-container">
                <h2>🚀 Ambassador Admin</h2>
                <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
                <form method="POST">
                    <input type="password" name="password" placeholder="Enter admin password" required>
                    <input type="submit" value="Login">
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", 
                   DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Handle status updates
    if (isset($_POST['update_status'])) {
        $ambassadorId = (int)$_POST['ambassador_id'];
        $newStatus = $_POST['status'] === 'active' ? 'active' : 'inactive';
        
        $updateStmt = $pdo->prepare("UPDATE ambassadors SET status = ? WHERE ambassador_id = ?");
        $updateStmt->execute([$newStatus, $ambassadorId]);
        
        $statusMessage = "Ambassador status updated successfully!";
    }
    
    // Get ambassadors with their stats
    $ambassadorsQuery = "
        SELECT 
            a.*,
            COUNT(t.id) as actual_referrals,
            a.referral_count as recorded_referrals
        FROM ambassadors a
        LEFT JOIN teams t ON a.ambassador_id = t.referred_by_ambassador
        GROUP BY a.ambassador_id
        ORDER BY a.referral_count DESC, a.name ASC
    ";
    
    $ambassadors = $pdo->query($ambassadorsQuery)->fetchAll();
    
    // Get summary stats
    $totalAmbassadors = count($ambassadors);
    $activeAmbassadors = count(array_filter($ambassadors, function($a) { return $a['status'] === 'active'; }));
    $totalReferrals = array_sum(array_column($ambassadors, 'actual_referrals'));
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ambassador_admin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambassador Program - Admin Panel</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #00ff7f 0%, #ffd700 100%);
            color: #0a0a0a;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 2rem;
        }
        .logout {
            float: right;
            color: #0a0a0a;
            text-decoration: none;
            background: rgba(0,0,0,0.1);
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
        }
        .logout:hover {
            background: rgba(0,0,0,0.2);
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        .leaderboard {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .leaderboard-header {
            background: #667eea;
            color: white;
            padding: 15px 20px;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .top-ambassador {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            padding: 20px;
            border-left: 5px solid #ff6b35;
        }
        .top-ambassador h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .ambassador-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
        .status-form {
            display: inline-block;
        }
        .status-select {
            padding: 4px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }
        .update-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-left: 5px;
        }
        .update-btn:hover {
            background: #0056b3;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .contact-info {
            font-size: 0.9rem;
            color: #666;
        }
        .motivation {
            font-size: 0.85rem;
            color: #666;
            font-style: italic;
            max-width: 300px;
            word-wrap: break-word;
        }
        @media (max-width: 768px) {
            .stats {
                grid-template-columns: 1fr 1fr;
            }
            table {
                font-size: 14px;
            }
            th, td {
                padding: 8px;
            }
            .motivation, .contact-info {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏆 Ambassador Program Admin</h1>
        <a href="?logout=1" class="logout">Logout</a>
        <div style="clear: both;"></div>
    </div>

    <?php if (isset($statusMessage)): ?>
    <div class="success-message">
        <?php echo $statusMessage; ?>
    </div>
    <?php endif; ?>

    <div class="stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo $totalAmbassadors; ?></div>
            <div class="stat-label">Total Ambassadors</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $activeAmbassadors; ?></div>
            <div class="stat-label">Active Ambassadors</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $totalReferrals; ?></div>
            <div class="stat-label">Total Referrals</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $totalReferrals > 0 ? number_format($totalReferrals / max($activeAmbassadors, 1), 1) : 0; ?></div>
            <div class="stat-label">Avg Referrals/Ambassador</div>
        </div>
    </div>

    <?php if (!empty($ambassadors)): ?>
    <div class="leaderboard">
        <div class="leaderboard-header">
            🥇 Current Leader
        </div>
        <?php $topAmbassador = $ambassadors[0]; ?>
        <div class="top-ambassador">
            <h3><?php echo htmlspecialchars($topAmbassador['name']); ?></h3>
            <p><strong><?php echo $topAmbassador['actual_referrals']; ?> successful referrals</strong></p>
            <p>Email: <?php echo htmlspecialchars($topAmbassador['email']); ?></p>
            <?php if ($topAmbassador['school_name']): ?>
            <p>School: <?php echo htmlspecialchars($topAmbassador['school_name']); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="ambassador-table">
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Name</th>
                    <th>Contact Info</th>
                    <th>School</th>
                    <th>Referrals</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th>Motivation</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ambassadors)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #666;">
                        No ambassadors registered yet.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($ambassadors as $index => $ambassador): ?>
                <tr>
                    <td>
                        <?php if ($index === 0): ?>
                            🥇 #1
                        <?php elseif ($index === 1): ?>
                            🥈 #2
                        <?php elseif ($index === 2): ?>
                            🥉 #3
                        <?php else: ?>
                            #<?php echo $index + 1; ?>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($ambassador['name']); ?></strong></td>
                    <td class="contact-info">
                        <strong>Email:</strong><br><?php echo htmlspecialchars($ambassador['email']); ?><br>
                        <?php if ($ambassador['phone']): ?>
                        <strong>Phone:</strong><br><?php echo htmlspecialchars($ambassador['phone']); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($ambassador['school_name'] ?: 'N/A'); ?></td>
                    <td>
                        <strong style="color: #007bff;"><?php echo $ambassador['actual_referrals']; ?></strong>
                        <?php if ($ambassador['actual_referrals'] != $ambassador['recorded_referrals']): ?>
                        <br><small style="color: #666;">(Recorded: <?php echo $ambassador['recorded_referrals']; ?>)</small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($ambassador['registration_date'])); ?></td>
                    <td>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="ambassador_id" value="<?php echo $ambassador['ambassador_id']; ?>">
                            <select name="status" class="status-select">
                                <option value="active" <?php echo $ambassador['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $ambassador['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                            <button type="submit" name="update_status" class="update-btn">Update</button>
                        </form>
                        <br><span class="status-<?php echo $ambassador['status']; ?>">
                            <?php echo ucfirst($ambassador['status']); ?>
                        </span>
                    </td>
                    <td class="motivation">
                        <?php echo htmlspecialchars($ambassador['motivation'] ?: 'No motivation provided'); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 30px; text-align: center; color: #666;">
        <p><strong>Prize:</strong> The ambassador with the most referrals wins 2 AoPS coupons worth $50 total</p>
        <p><a href="admin.php" style="color: #667eea;">← Back to Registration Admin</a></p>
    </div>

</body>
</html>