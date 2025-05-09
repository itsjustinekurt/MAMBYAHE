<?php
session_start();
header('Content-Type: application/json');

$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $response = [
        'success' => true,
        'message' => '',
        'table_exists' => false,
        'columns' => [],
        'notifications_count' => 0
    ];

    // Check if notifications table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'notifications'")->rowCount() > 0;
    $response['table_exists'] = $tableExists;
    
    if (!$tableExists) {
        // Create notifications table
        $pdo->exec("
            CREATE TABLE notifications (
                notification_id INT AUTO_INCREMENT PRIMARY KEY,
                driver_id INT NOT NULL,
                booking_id INT,
                passenger_id INT,
                user_type ENUM('driver', 'passenger') NOT NULL,
                type VARCHAR(50) NOT NULL,
                message TEXT NOT NULL,
                status ENUM('unread', 'read') DEFAULT 'unread',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (driver_id) REFERENCES driver(driver_id),
                FOREIGN KEY (booking_id) REFERENCES bookings(id),
                FOREIGN KEY (passenger_id) REFERENCES passenger(passenger_id)
            )
        ");
        $response['message'] = 'Notifications table created';
    } else {
        // Check table structure
        $response['columns'] = $pdo->query("DESCRIBE notifications")->fetchAll(PDO::FETCH_COLUMN);
        $response['message'] = 'Notifications table exists';
    }

    // Check for any notifications
    if (isset($_SESSION['driver_id'])) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM notifications 
            WHERE driver_id = :driver_id
            AND user_type = 'driver'
            AND status = 'unread'
        ");
        $stmt->execute(['driver_id' => $_SESSION['driver_id']]);
        $response['notifications_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 