<?php
require_once '../db_connect.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS driver_location (
        id INT PRIMARY KEY AUTO_INCREMENT,
        driver_id INT NOT NULL,
        latitude DECIMAL(10,8) NOT NULL,
        longitude DECIMAL(11,8) NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (driver_id) REFERENCES driver(driver_id)
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Driver location table created successfully";
    } else {
        echo "Error creating table: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
