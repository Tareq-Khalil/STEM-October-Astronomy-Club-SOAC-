<?php
// admin.php - Secure admin panel to view registrations
define('SECURE_ACCESS', true);

// Include config file (stored outside public directory)
require_once '../config.php'; // Adjust path as needed

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
            <title>Admin Login - Cosmic Quest</title>
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
                <h2>🚀 Cosmic Quest Admin</h2>
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

// Database connection using config constants
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", 
                   DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Get filter parameters
    $search = $_GET['search'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    
    // Build query with filters
    $query = "SELECT * FROM team_registrations";
    $params = [];
    $where_conditions = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(team_name LIKE ? OR school_name LIKE ? OR members_info LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if (!empty($date_from)) {
        $where_conditions[] = "registration_date >= ?";
        $params[] = $date_from;
    }
    
    if (!empty($date_to)) {
        $where_conditions[] = "registration_date <= ?";
        $params[] = $date_to . ' 23:59:59';
    }
    
    if (!empty($where_conditions)) {
        $query .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $registrations = $stmt->fetchAll();
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM teams";
    $total_registrations = $pdo->query($count_query)->fetch()['total'];
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cosmic Quest - Registration Admin Panel</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .stats {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .filters form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        .filters input, .filters button {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .filters button {
            background: #667eea;
            color: white;
            cursor: pointer;
            border: none;
        }
        .filters button:hover {
            background: #5a67d8;
        }
        .table-container {
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
        .member-info {
            font-size: 12px;
            color: #666;
            max-width: 300px;
        }
        .contact-info {
            font-size: 12px;
            color: #666;
            max-width: 200px;
            word-break: break-word;
        }
        .logout {
            float: right;
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 4px;
        }
        .logout:hover {
            background: rgba(255,255,255,0.3);
        }
        @media (max-width: 768px) {
            table {
                font-size: 12px;
            }
            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚀 Cosmic Quest - Registration Admin Panel</h1>
        <a href="?logout=1" class="logout">Logout</a>
        <div style="clear: both;"></div>
    </div>

    <div class="stats">
        <h3>📊 Statistics</h3>
        <p><strong>Total Registrations:</strong> <?php echo $total_registrations; ?></p>
        <p><strong>Showing:</strong> <?php echo count($registrations); ?> registrations</p>
    </div>

    <div class="filters">
        <h3>🔍 Search & Filter</h3>
        <form method="GET">
            <div>
                <label>Search (Team/School/Members):</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Enter search term...">
            </div>
            <div>
                <label>From Date:</label>
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            </div>
            <div>
                <label>To Date:</label>
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            </div>
            <div>
                <button type="submit">Apply Filters</button>
            </div>
            <div>
                <button type="button" onclick="window.location.href='admin.php'">Clear All</button>
            </div>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Team Name</th>
                    <th>School Name</th>
                    <th>Registration Date</th>
                    <th>Members</th>
                    <th>Contact Info</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($registrations)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                        No registrations found matching your criteria.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($registrations as $reg): ?>
                <tr>
                    <td><strong>#<?php echo $reg['team_id']; ?></strong></td>
                    <td><strong><?php echo htmlspecialchars($reg['team_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($reg['school_name']); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($reg['registration_date'])); ?></td>
                    <td class="member-info">
                        <strong><?php echo $reg['member_count']; ?> member(s)</strong><br>
                        <?php echo nl2br(htmlspecialchars($reg['members_info'])); ?>
                    </td>
                    <td class="contact-info">
                        <strong>Emails:</strong><br><?php echo htmlspecialchars($reg['emails'] ?: 'N/A'); ?><br><br>
                        <strong>Phones:</strong><br><?php echo htmlspecialchars($reg['phones'] ?: 'N/A'); ?>
                    </td>
                    <td><small><?php echo htmlspecialchars($reg['user_ip']); ?></small></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>