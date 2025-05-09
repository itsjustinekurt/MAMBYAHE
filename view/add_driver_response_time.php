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

    // Check if driver_response_time column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'driver_response_time'");
    if ($stmt->rowCount() == 0) {
        // Add the column if it doesn't exist
        $pdo->exec("ALTER TABLE bookings ADD COLUMN driver_response_time TIMESTAMP NULL DEFAULT NULL");
        echo json_encode([
            'success' => true,
            'message' => 'Added driver_response_time column to bookings table'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'driver_response_time column already exists'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 