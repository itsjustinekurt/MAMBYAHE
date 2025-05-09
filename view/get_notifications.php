<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to JSON
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if passenger is logged in
if (!isset($_SESSION['passenger_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Database credentials
require_once '../config/database.php';

// Log connection attempt
error_log("Attempting to connect to database");

try {
    // Fetch notifications for the passenger
    $stmt = $pdo->prepare("
        SELECT n.*, d.fullname as driver_name, d.username as driver_username, 
               d.plate_no as plate_number, d.profile_pic as driver_pic, 
               b.id as booking_id, b.pickup, b.destination, b.seats, b.fare as total_fare,
               DATE_FORMAT(b.created_at, '%Y-%m-%d') as booking_date, 
               DATE_FORMAT(b.created_at, '%H:%i:%s') as booking_time
        FROM notifications n 
        LEFT JOIN driver d ON n.driver_id = d.driver_id
        LEFT JOIN bookings b ON n.booking_id = b.id
        WHERE n.passenger_id = :passenger_id 
        AND n.user_type = 'passenger'
        ORDER BY n.created_at DESC 
        LIMIT 10
    ");
    
    $stmt->execute(['passenger_id' => $_SESSION['passenger_id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);

} catch (PDOException $e) {
    // Log detailed database error
    error_log("Database error in get_notifications.php: " . $e->getMessage());
    error_log("Error code: " . $e->getCode());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Return user-friendly error message
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
