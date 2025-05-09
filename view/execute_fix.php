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

    // Execute the ALTER TABLE command directly
    $sql = "ALTER TABLE bookings ADD COLUMN driver_response_time TIMESTAMP NULL DEFAULT NULL";
    $pdo->exec($sql);

    echo json_encode([
        'success' => true,
        'message' => 'Column driver_response_time added successfully'
    ]);

} catch (PDOException $e) {
    // If the column already exists, that's fine
    if ($e->getCode() == '42S21') {
        echo json_encode([
            'success' => true,
            'message' => 'Column driver_response_time already exists'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?> 