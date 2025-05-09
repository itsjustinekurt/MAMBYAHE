<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials
$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if table already exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'driver_reviews'")->rowCount() > 0;
    
    if ($tableExists) {
        echo "<div style='color: green;'>Table 'driver_reviews' already exists.</div>";
    } else {
        // SQL to create driver_reviews table
        $sql = "CREATE TABLE driver_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            driver_id INT NOT NULL,
            passenger_id INT NOT NULL,
            booking_id INT NOT NULL,
            rating INT NOT NULL,
            review TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (driver_id) REFERENCES driver(driver_id) ON DELETE CASCADE,
            FOREIGN KEY (passenger_id) REFERENCES passenger(passenger_id) ON DELETE CASCADE,
            FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
            UNIQUE KEY unique_booking_review (booking_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        // Execute the SQL
        $pdo->exec($sql);
        echo "<div style='color: green;'>Table 'driver_reviews' created successfully!</div>";
    }

    // Verify table structure
    $columns = $pdo->query("DESCRIBE driver_reviews")->fetchAll(PDO::FETCH_ASSOC);
    echo "<div style='margin-top: 20px;'>";
    echo "<h3>Table Structure:</h3>";
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='color: red;'>";
    echo "<h3>Error Details:</h3>";
    echo "<p>Error Code: " . $e->getCode() . "</p>";
    echo "<p>Error Message: " . $e->getMessage() . "</p>";
    
    // Additional error information
    if ($e->getCode() == '42S01') {
        echo "<p>The table already exists.</p>";
    } elseif ($e->getCode() == '42S02') {
        echo "<p>One of the referenced tables (driver, passenger, or bookings) does not exist.</p>";
    }
    echo "</div>";
}
?> 