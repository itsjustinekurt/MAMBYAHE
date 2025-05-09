<?php
require_once '../config/database.php';

try {
    // Create MTFRB complaints table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS mtfrb_complaints (
            complaint_id INT PRIMARY KEY AUTO_INCREMENT,
            passenger_id INT NOT NULL,
            driver_name VARCHAR(100) NOT NULL,
            plate_number VARCHAR(20) NOT NULL,
            incident_date DATE NOT NULL,
            incident_time TIME NOT NULL,
            pickup_location VARCHAR(255) NOT NULL,
            destination VARCHAR(255) NOT NULL,
            rejection_reason TEXT NOT NULL,
            complaint_type ENUM('discriminatory', 'unprofessional', 'inappropriate', 'no_show', 'other') NOT NULL,
            complaint_details TEXT NOT NULL,
            status ENUM('pending', 'under_review', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending',
            admin_remarks TEXT,
            created_at DATETIME NOT NULL,
            updated_at DATETIME,
            FOREIGN KEY (passenger_id) REFERENCES passenger(passenger_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Create complaint documents table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS complaint_documents (
            document_id INT PRIMARY KEY AUTO_INCREMENT,
            complaint_id INT NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            file_type VARCHAR(100) NOT NULL,
            file_size INT NOT NULL,
            uploaded_at DATETIME NOT NULL,
            FOREIGN KEY (complaint_id) REFERENCES mtfrb_complaints(complaint_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Create complaint status history table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS complaint_status_history (
            history_id INT PRIMARY KEY AUTO_INCREMENT,
            complaint_id INT NOT NULL,
            status ENUM('pending', 'under_review', 'resolved', 'dismissed') NOT NULL,
            remarks TEXT,
            updated_by INT NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (complaint_id) REFERENCES mtfrb_complaints(complaint_id) ON DELETE CASCADE,
            FOREIGN KEY (updated_by) REFERENCES admin(admin_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    echo "Tables created successfully!";
} catch (PDOException $e) {
    die("Error creating tables: " . $e->getMessage());
}
?> 