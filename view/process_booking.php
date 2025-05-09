<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['passenger_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

// Validate required fields
$required_fields = ['pickup', 'destination', 'seats', 'driver_id', 'total_fare'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // First, check if the driver is still available
    $stmt = $pdo->prepare("
        SELECT d.driver_id, d.fullname, d.status, d.is_online
        FROM driver d 
        WHERE d.driver_id = :driver_id 
        AND d.status = 'approved'
        AND d.is_online = 'online'
        AND d.driver_id NOT IN (
            SELECT b.driver_id 
            FROM bookings b 
            WHERE b.status IN ('pending', 'accepted', 'in_progress')
        )
    ");
    
    $stmt->execute(['driver_id' => $data['driver_id']]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$driver) {
        throw new Exception('Selected driver is no longer available. Please select another driver.');
    }

    // Insert booking
    $stmt = $pdo->prepare("
        INSERT INTO bookings (
            passenger_id, 
            driver_id, 
            pickup, 
            destination, 
            seats, 
            fare, 
            status, 
            created_at
        ) VALUES (
            :passenger_id,
            :driver_id,
            :pickup,
            :destination,
            :seats,
            :fare,
            'pending',
            NOW()
        )
    ");

    $stmt->execute([
        'passenger_id' => $_SESSION['passenger_id'],
        'driver_id' => $data['driver_id'],
        'pickup' => $data['pickup'],
        'destination' => $data['destination'],
        'seats' => $data['seats'],
        'fare' => $data['total_fare']
    ]);

    $booking_id = $pdo->lastInsertId();

    // Get passenger details for the notification
    $stmt = $pdo->prepare("SELECT username FROM passenger WHERE passenger_id = :passenger_id");
    $stmt->execute(['passenger_id' => $_SESSION['passenger_id']]);
    $passenger = $stmt->fetch(PDO::FETCH_ASSOC);

    // Create notification for driver
    $stmt = $pdo->prepare("
        INSERT INTO notifications (
            passenger_id,
            driver_id,
            booking_id,
            type,
            message,
            status,
            user_type,
            created_at
        ) VALUES (
            :passenger_id,
            :driver_id,
            :booking_id,
            'New Booking Request',
            :message,
            'unread',
            'driver',
            NOW()
        )
    ");

    $message = sprintf(
        "New booking request from %s\nPickup: %s\nDestination: %s\nSeats: %d\nTotal Fare: ₱%.2f",
        $passenger['username'],
        $data['pickup'],
        $data['destination'],
        $data['seats'],
        $data['total_fare']
    );

    $stmt->execute([
        'passenger_id' => $_SESSION['passenger_id'],
        'driver_id' => $data['driver_id'],
        'booking_id' => $booking_id,
        'message' => $message
    ]);

    // Create notification for passenger
    $stmt = $pdo->prepare("
        INSERT INTO notifications (
            passenger_id,
            driver_id,
            booking_id,
            type,
            message,
            status,
            user_type,
            created_at
        ) VALUES (
            :passenger_id,
            :driver_id,
            :booking_id,
            'Booking Request Sent',
            :message,
            'unread',
            'passenger',
            NOW()
        )
    ");

    $passengerMessage = sprintf(
        "Your booking request has been sent to %s\nPickup: %s\nDestination: %s\nSeats: %d\nTotal Fare: ₱%.2f",
        $driver['fullname'],
        $data['pickup'],
        $data['destination'],
        $data['seats'],
        $data['total_fare']
    );

    $stmt->execute([
        'passenger_id' => $_SESSION['passenger_id'],
        'driver_id' => $data['driver_id'],
        'booking_id' => $booking_id,
        'message' => $passengerMessage
    ]);

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully',
        'booking_id' => $booking_id
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    
    error_log("Error in process_booking.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}