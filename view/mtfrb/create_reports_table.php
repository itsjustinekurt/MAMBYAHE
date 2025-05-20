<?php
session_start();
require_once '../db_connect.php';

try {
    // Create reports table
    $sql = "CREATE TABLE IF NOT EXISTS reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reported_id INT NOT NULL,
        complaint VARCHAR(255) NOT NULL,
        reason TEXT NOT NULL,
        status ENUM('pending', 'resolved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (reported_id) REFERENCES passengers(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Reports table created successfully";
    } else {
        echo "Error creating table: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
