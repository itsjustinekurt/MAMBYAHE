<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
});
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "$errstr in $errfile on line $errline"]);
    exit();
});

// Check if driver is logged in
if (!isset($_SESSION['driver_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Get JSON data from request
$raw_data = file_get_contents('php://input');
error_log("Raw request data: " . $raw_data);
$data = json_decode($raw_data, true);
error_log("Decoded data: " . print_r($data, true));

if (!isset($data['booking_id']) || !isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Database credentials
$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

require_once __DIR__ . '/send_booking_notification.php';

try {
    // Establish PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    // First, verify the booking exists and belongs to this driver
    $query = "
        SELECT b.*, p.passenger_id, p.fullname as passenger_name 
            FROM bookings b 
            JOIN passenger p ON b.passenger_name = p.fullname 
            WHERE b.id = :booking_id AND b.driver_id = :driver_id
    ";
    error_log("SQL Query: " . $query);
    error_log("Parameters: booking_id=" . $data['booking_id'] . ", driver_id=" . $_SESSION['driver_id']);
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'booking_id' => $data['booking_id'],
        'driver_id' => $_SESSION['driver_id']
    ]);
    
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("Booking data: " . print_r($booking, true));

    if (!$booking) {
        // Let's check if the booking exists at all
        $check_stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = :booking_id");
        $check_stmt->execute(['booking_id' => $data['booking_id']]);
        $exists = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$exists) {
            throw new Exception('Booking not found');
        } else {
            throw new Exception('Booking exists but is not authorized for this driver');
        }
    }

    // Update booking status
    $new_status = $data['action'] === 'confirm' ? 'accepted' : 'cancelled';
    $stmt = $pdo->prepare("
        UPDATE bookings 
    SET status = :status, 
        driver_response_time = NOW() 
        WHERE id = :booking_id
    AND driver_id = :driver_id
    ");
    
    $stmt->execute([
        'status' => $new_status,
        'booking_id' => $data['booking_id'],
        'driver_id' => $_SESSION['driver_id']
    ]);

    if ($data['action'] === 'reject' || $new_status === 'cancelled') {
        $rejection_reason = isset($data['reason']) ? $data['reason'] : 'No reason provided';
        sendBookingNotification($pdo, $data['booking_id'], 'cancelled', $_SESSION['driver_id'], $rejection_reason);
    } else if ($data['action'] === 'confirm' || $new_status === 'accepted') {
        sendBookingNotification($pdo, $data['booking_id'], 'accepted', $_SESSION['driver_id']);
    }

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Booking ' . $new_status . ' successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction if started
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Error in handle_booking.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
?> 