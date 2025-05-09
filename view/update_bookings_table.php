<?php
// Database credentials
$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

try {
    // Establish PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // First, check if the table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'bookings'");
    if ($stmt->rowCount() == 0) {
        // Create bookings table if it doesn't exist
        $pdo->exec("CREATE TABLE bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            passenger_name VARCHAR(255) NOT NULL,
            pickup VARCHAR(255) NOT NULL,
            destination VARCHAR(255) NOT NULL,
            seats INT NOT NULL,
            booking_date DATE NOT NULL,
            booking_time TIME NOT NULL,
            fare DECIMAL(10,2) NOT NULL,
            driver_id INT,
            status ENUM('pending', 'accepted', 'cancelled', 'completed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            driver_response_time TIMESTAMP NULL,
            FOREIGN KEY (driver_id) REFERENCES driver(driver_id) ON DELETE SET NULL
        )");
        echo "Bookings table created successfully\n";
    } else {
        // Check if booking_date column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'booking_date'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE bookings ADD COLUMN booking_date DATE NOT NULL AFTER seats");
            echo "Added booking_date column\n";
        }

        // Check if booking_time column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'booking_time'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE bookings ADD COLUMN booking_time TIME NOT NULL AFTER booking_date");
            echo "Added booking_time column\n";
        }

        // Check if driver_response_time column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'driver_response_time'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE bookings ADD COLUMN driver_response_time TIMESTAMP NULL AFTER created_at");
            echo "Added driver_response_time column\n";
        }

        // Check if status column exists and has the correct ENUM values
        $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'status'");
        if ($stmt->rowCount() > 0) {
            $pdo->exec("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'accepted', 'cancelled', 'completed') DEFAULT 'pending'");
            echo "Updated status column\n";
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Database structure updated successfully'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 