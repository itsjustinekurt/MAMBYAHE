<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['passenger_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

try {
    // Validate required fields
    $required_fields = ['pickup', 'destination', 'seats', 'booking_date', 'booking_time', 'fare'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            exit();
        }
    }

    // Insert booking into database
    $stmt = $pdo->prepare("
        INSERT INTO bookings (
            passenger_id, pickup, dropoff, seats, 
            booking_date, booking_time, total_fare, 
            status, created_at
        ) VALUES (
            :passenger_id, :pickup, :dropoff, :seats,
            :booking_date, :booking_time, :total_fare,
            'pending', NOW()
        )
    ");

    $stmt->execute([
        'passenger_id' => $_SESSION['passenger_id'],
        'pickup' => $data['pickup'],
        'dropoff' => $data['destination'],
        'seats' => $data['seats'],
        'booking_date' => $data['booking_date'],
        'booking_time' => $data['booking_time'],
        'total_fare' => $data['fare']
    ]);

    $booking_id = $pdo->lastInsertId();

    // Create notification for available drivers
    $stmt = $pdo->prepare("
        INSERT INTO notifications (
            driver_id, passenger_id, type, message, 
            status, created_at, user_type
        )
        SELECT 
            d.driver_id, :passenger_id, 'New Booking',
            'You have a new booking request.',
            'unread', NOW(), 'driver'
        FROM driver d
        WHERE d.is_online = 'online'
        AND d.status = 'approved'
    ");

    $stmt->execute([
        'passenger_id' => $_SESSION['passenger_id']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully',
        'booking_id' => $booking_id
    ]);

} catch (PDOException $e) {
    error_log("Booking creation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error creating booking: ' . $e->getMessage()
    ]);
}
?> 