<?php
// test.php - Upload to public_html and visit soac.club/test.php
// DELETE THIS FILE AFTER TESTING!

echo "<h1>🔧 Air Quality Monitor Debug</h1>";
echo "<hr>";

// Test 1: PHP Version
echo "<h2>1. PHP Version Check</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo (version_compare(phpversion(), '7.0', '>=') ? "✅ PHP version OK" : "❌ PHP version too old") . "<br><br>";

// Test 2: Required Extensions
echo "<h2>2. Required Extensions</h2>";
echo "PDO: " . (extension_loaded('pdo') ? "✅ Available" : "❌ Missing") . "<br>";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? "✅ Available" : "❌ Missing") . "<br>";
echo "JSON: " . (extension_loaded('json') ? "✅ Available" : "❌ Missing") . "<br><br>";

// Test 3: Config File
echo "<h2>3. Config File Test</h2>";
$configPath = '../config12.php';
if (file_exists($configPath)) {
    echo "✅ config12.php found<br>";
    
    try {
        require_once $configPath;
        echo "✅ Config file loaded successfully<br>";
        
        // Test database connection
        echo "<h2>4. Database Connection Test</h2>";
        $stmt = $pdo->query("SELECT 1");
        echo "✅ Database connection successful<br>";
        
        // Test table existence
        echo "<h2>5. Table Structure Test</h2>";
        $stmt = $pdo->query("SHOW TABLES LIKE 'sensor_data'");
        if ($stmt->rowCount() > 0) {
            echo "✅ sensor_data table exists<br>";
            
            // Check table structure
            $stmt = $pdo->query("DESCRIBE sensor_data");
            $columns = $stmt->fetchAll();
            echo "Table columns: " . count($columns) . "<br>";
            
            // Count records
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM sensor_data");
            $result = $stmt->fetch();
            echo "Records in table: " . $result['count'] . "<br>";
            
        } else {
            echo "❌ sensor_data table missing<br>";
            echo "<strong>Solution:</strong> Run this SQL in phpMyAdmin:<br>";
            echo "<textarea style='width:100%;height:200px;'>";
            echo "CREATE TABLE sensor_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sensor1_co FLOAT NOT NULL,
    sensor1_no2 FLOAT NOT NULL,
    sensor1_no FLOAT NOT NULL,
    sensor1_so2 FLOAT NOT NULL,
    sensor1_voc FLOAT NOT NULL,
    sensor1_fan_status TINYINT(1) DEFAULT 0,
    sensor2_co FLOAT NOT NULL,
    sensor2_no2 FLOAT NOT NULL,
    sensor2_no FLOAT NOT NULL,
    sensor2_so2 FLOAT NOT NULL,
    sensor2_voc FLOAT NOT NULL,
    sensor2_fan_status TINYINT(1) DEFAULT 0,
    INDEX idx_timestamp (timestamp)
);";
            echo "</textarea><br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Database error: " . $e->getMessage() . "<br>";
        echo "<strong>Check:</strong> Database credentials in config12.php<br>";
    }
    
} else {
    echo "❌ config12.php not found<br>";
    echo "<strong>Expected location:</strong> " . realpath('../') . "/config12.php<br>";
}

// Test 4: API File
echo "<h2>6. API Files Test</h2>";
$apiPath = 'api/store_data.php';
if (file_exists($apiPath)) {
    echo "✅ API file exists at: $apiPath<br>";
} else {
    echo "❌ API file missing<br>";
    echo "<strong>Solution:</strong> Create 'api' folder and upload store_data.php<br>";
}

// Test 5: File Permissions
echo "<h2>7. File Permissions</h2>";
echo "Current directory: " . getcwd() . "<br>";
echo "Can write to current dir: " . (is_writable('.') ? "✅ Yes" : "❌ No") . "<br>";

echo "<hr>";
echo "<p><strong>🔒 IMPORTANT:</strong> Delete this file after testing for security!</p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h1, h2 { color: #333; }
textarea { font-family: monospace; font-size: 12px; }
</style>