<?php
// setup_database.php - Run this once to create the database table
// Place in public_html and run via browser, then delete for security

require_once '../config12.php';

try {
    // Create the sensor_data table
    $sql = "CREATE TABLE IF NOT EXISTS sensor_data (
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
    )";
    
    $pdo->exec($sql);
    
    echo "<h1>✅ Database Setup Complete!</h1>";
    echo "<p>The sensor_data table has been created successfully.</p>";
    echo "<p><strong>Important:</strong> Delete this file after running it for security reasons.</p>";
    echo "<p>You can now start using your air quality monitoring system.</p>";
    
    // Optional: Insert sample data for testing
    if (isset($_GET['sample']) && $_GET['sample'] == 'true') {
        echo "<h2>Inserting sample data...</h2>";
        
        $sampleData = $pdo->prepare("
            INSERT INTO sensor_data (
                sensor1_co, sensor1_no2, sensor1_no, sensor1_so2, sensor1_voc, sensor1_fan_status,
                sensor2_co, sensor2_no2, sensor2_no, sensor2_so2, sensor2_voc, sensor2_fan_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Generate 50 sample records
        for ($i = 0; $i < 50; $i++) {
            $sampleData->execute([
                rand(50, 150) / 10,  // CO sensor 1
                rand(10, 50) / 100,  // NO2 sensor 1
                rand(5, 25) / 1000,  // NO sensor 1
                rand(20, 80) / 100,  // SO2 sensor 1
                rand(100, 500) / 10, // VOC sensor 1
                rand(0, 1),          // Fan 1 status
                rand(30, 120) / 10,  // CO sensor 2 (lower after filtration)
                rand(5, 30) / 100,   // NO2 sensor 2
                rand(2, 15) / 1000,  // NO sensor 2
                rand(10, 60) / 100,  // SO2 sensor 2
                rand(50, 300) / 10,  // VOC sensor 2
                rand(0, 1)           // Fan 2 status
            ]);
        }
        
        echo "<p>✅ Sample data inserted successfully!</p>";
    }
    
} catch(PDOException $e) {
    echo "<h1>❌ Database Setup Failed!</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        h1 { color: #2c3e50; }
        p { line-height: 1.6; }
        .button { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <?php if (!isset($_GET['sample'])): ?>
    <a href="?sample=true" class="button">Insert Sample Data for Testing</a>
    <?php endif; ?>
</body>
</html>