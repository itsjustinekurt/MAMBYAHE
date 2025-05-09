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

    // Add booking_date and booking_time columns if they don't exist
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'booking_date'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN booking_date DATE NULL");
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'booking_time'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN booking_time TIME NULL");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Date and time columns added successfully'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>