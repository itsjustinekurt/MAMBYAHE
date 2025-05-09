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

    // Start transaction
    $pdo->beginTransaction();

    // Check if bookings table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'bookings'");
    if ($stmt->rowCount() == 0) {
        throw new Exception("Bookings table does not exist");
    }

    // Get current columns
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    error_log("Current columns: " . print_r($columns, true));

    // Add missing columns if they don't exist
    $required_columns = [
        'driver_response_time' => "ALTER TABLE bookings ADD COLUMN driver_response_time TIMESTAMP NULL DEFAULT NULL",
        'status' => "ALTER TABLE bookings ADD COLUMN status VARCHAR(20) DEFAULT 'pending'",
        'driver_id' => "ALTER TABLE bookings ADD COLUMN driver_id INT NULL",
        'passenger_name' => "ALTER TABLE bookings ADD COLUMN passenger_name VARCHAR(255) NULL",
        'pickup' => "ALTER TABLE bookings ADD COLUMN pickup VARCHAR(255) NULL",
        'destination' => "ALTER TABLE bookings ADD COLUMN destination VARCHAR(255) NULL",
        'seats' => "ALTER TABLE bookings ADD COLUMN seats INT NULL",
        'fare' => "ALTER TABLE bookings ADD COLUMN fare DECIMAL(10,2) NULL"
    ];

    foreach ($required_columns as $column => $sql) {
        if (!in_array($column, $columns)) {
            error_log("Adding column: " . $column);
            $pdo->exec($sql);
        }
    }

    // Add foreign key constraints if they don't exist
    $stmt = $pdo->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = '$db_name' 
        AND TABLE_NAME = 'bookings' 
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ");
    $constraints = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('bookings_driver_id_fk', $constraints)) {
        $pdo->exec("
            ALTER TABLE bookings 
            ADD CONSTRAINT bookings_driver_id_fk 
            FOREIGN KEY (driver_id) REFERENCES driver(driver_id) 
            ON DELETE SET NULL
        ");
    }

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Bookings table structure updated successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction if started
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error fixing bookings table: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 