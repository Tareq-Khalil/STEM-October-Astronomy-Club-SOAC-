<?php
// debug_database.php - Check what tables and columns exist
define('SECURE_ACCESS', true);
require_once '../config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", 
                   DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "<h2>Database Debug Information</h2>";
    echo "<h3>Database: " . DB_NAME . "</h3>";
    
    // Show all tables
    echo "<h3>All Tables:</h3>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tables)) {
        echo "<p style='color: red;'>No tables found in database!</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }
    
    // Check ambassadors table structure
    if (in_array('ambassadors', $tables)) {
        echo "<h3>Ambassadors Table Structure:</h3>";
        $columns = $pdo->query("DESCRIBE ambassadors")->fetchAll();
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Count ambassadors
        $count = $pdo->query("SELECT COUNT(*) FROM ambassadors")->fetchColumn();
        echo "<p>Total ambassadors: $count</p>";
    } else {
        echo "<p style='color: red;'>Ambassadors table does not exist!</p>";
    }
    
    // Check teams table structure
    if (in_array('teams', $tables)) {
        echo "<h3>Teams Table Structure:</h3>";
        $columns = $pdo->query("DESCRIBE teams")->fetchAll();
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Count teams
        $count = $pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn();
        echo "<p>Total teams: $count</p>";
    } else {
        echo "<p style='color: red;'>Teams table does not exist!</p>";
    }
    
    // Check team_members table structure
    if (in_array('team_members', $tables)) {
        echo "<h3>Team Members Table Structure:</h3>";
        $columns = $pdo->query("DESCRIBE team_members")->fetchAll();
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Count team members
        $count = $pdo->query("SELECT COUNT(*) FROM team_members")->fetchColumn();
        echo "<p>Total team members: $count</p>";
    } else {
        echo "<p style='color: red;'>Team_members table does not exist!</p>";
    }
    
    // Test the problematic query
    echo "<h3>Testing Problematic Query:</h3>";
    try {
        $testQuery = "
            SELECT 
                a.*,
                COUNT(t.team_id) as actual_referrals,
                a.referral_count as recorded_referrals
            FROM ambassadors a
            LEFT JOIN teams t ON a.ambassador_id = t.referred_by_ambassador
            GROUP BY a.ambassador_id
            ORDER BY a.referral_count DESC, a.name ASC
            LIMIT 1
        ";
        
        $result = $pdo->query($testQuery)->fetch();
        echo "<p style='color: green;'>Query executed successfully!</p>";
        if ($result) {
            echo "<p>Sample result: " . json_encode($result, JSON_PRETTY_PRINT) . "</p>";
        } else {
            echo "<p>No results returned (probably no data in tables)</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Query failed: " . $e->getMessage() . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database connection error: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr><p><strong>Instructions:</strong></p>";
echo "<ol>";
echo "<li>Run this file to see what tables exist</li>";
echo "<li>If tables are missing, run the SQL schema I provided</li>";
echo "<li>If tables exist but have different column names, we'll need to adjust the code</li>";
echo "</ol>";
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { margin: 10px 0; }
    th { background: #f0f0f0; padding: 8px; }
    td { padding: 8px; }
    h3 { color: #333; border-bottom: 2px solid #ccc; }
</style>