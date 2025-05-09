<?php
session_start();

// Check if driver is logged in
if (!isset($_SESSION['driver_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['booking_id']) || !isset($data['reason'])) {
    echo json_encode(['success' => false, 'message' => 'Booking ID and reason are required']);
    exit();
}

// Database credentials
$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

try {
    // Establish PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if rejection_reason column exists, if not add it
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'rejection_reason'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN rejection_reason TEXT NULL AFTER status");
    }

    // Start transaction
    $pdo->beginTransaction();

    // Get booking details with passenger information
    $stmt = $pdo->prepare("
        SELECT b.id, b.status, b.passenger_name, p.passenger_id, p.fullname
        FROM bookings b 
        LEFT JOIN passenger p ON b.passenger_name = p.fullname 
        LEFT JOIN notifications n ON n.message LIKE CONCAT('%', b.passenger_name, '%')
        WHERE n.notification_id = :notification_id 
        AND b.driver_id = :driver_id
        AND b.status = 'pending'
    ");
    $stmt->execute([
        'notification_id' => $data['booking_id'],
        'driver_id' => $_SESSION['driver_id']
    ]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        throw new Exception('Booking not found or already processed');
    }

    if (!$booking['passenger_id']) {
        throw new Exception('Could not find passenger information');
    }

    // Update booking status
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'rejected', rejection_reason = :reason WHERE id = :booking_id");
    $stmt->execute([
        'reason' => $data['reason'],
        'booking_id' => $booking['id']
    ]);

    // Create notification for the specific passenger
    $stmt = $pdo->prepare("
        INSERT INTO notifications (
            notification_id, 
            user_type,
            driver_id, 
            passenger_id, 
            type, 
            message, 
            status, 
            created_at
        ) VALUES (
            NULL,
            'passenger',
            :driver_id, 
            :passenger_id, 
            'rejection', 
            :message, 
            'unread', 
            NOW()
        )
    ");

    // Create a personalized message for the passenger
    $message = sprintf(
        "Your ride request has been rejected by the driver. Reason: %s",
        $data['reason']
    );

    $stmt->execute([
        'driver_id' => $_SESSION['driver_id'],
        'passenger_id' => $booking['passenger_id'],
        'message' => $message
    ]);

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Booking rejected successfully'
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 