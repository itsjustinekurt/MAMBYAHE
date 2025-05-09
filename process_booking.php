<?php
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database credentials
$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

try {
    // Get JSON data from request
    $json = file_get_contents('php://input');
    error_log("Received JSON data: " . $json);
    
    $data = json_decode($json, true);
    if (!$data) {
        throw new Exception('Invalid JSON data received: ' . json_last_error_msg());
    }

    // Log received data
    error_log("Decoded data: " . print_r($data, true));

    // Validate required fields
    $required_fields = ['pickup', 'dropoff', 'seats', 'driver_id', 'total_fare', 'passenger_id'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Connect to database
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Start transaction
    $pdo->beginTransaction();

    // Get passenger name
    $stmt = $pdo->prepare("SELECT fullname FROM passenger WHERE passenger_id = :passenger_id");
    $stmt->execute(['passenger_id' => $data['passenger_id']]);
    $passenger = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$passenger) {
        throw new Exception('Passenger not found with ID: ' . $data['passenger_id']);
    }

    // Verify driver is still available
    $stmt = $pdo->prepare("SELECT fullname, is_online, status FROM driver WHERE driver_id = :driver_id");
    $stmt->execute(['driver_id' => $data['driver_id']]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    error_log("Driver status check - Driver data: " . print_r($driver, true));

    if (!$driver) {
        throw new Exception('Driver not found with ID: ' . $data['driver_id']);
    }

    if ($driver['is_online'] !== 1) {
        throw new Exception('Selected driver is offline. Driver ID: ' . $data['driver_id']);
    }

    if ($driver['status'] !== 'approved') {
        throw new Exception('Selected driver is not approved. Driver ID: ' . $data['driver_id']);
    }

    // First, let's check the actual table structure
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    error_log("Bookings table columns: " . print_r($columns, true));

    // Check if the table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'bookings'");
    if ($stmt->rowCount() === 0) {
        throw new Exception('Bookings table does not exist');
    }

    // Insert booking
    try {
        $stmt = $pdo->prepare("
            INSERT INTO bookings (
                passenger_name, 
                pickup, 
                destination, 
                seats, 
                fare, 
                driver_id,
                status
            ) VALUES (
                :passenger_name,
                :pickup,
                :destination,
                :seats,
                :fare,
                :driver_id,
                'pending'
            )
        ");

        $bookingParams = [
            'passenger_name' => $passenger['fullname'],
            'pickup' => $data['pickup'],
            'destination' => $data['dropoff'],
            'seats' => $data['seats'],
            'fare' => $data['total_fare'],
            'driver_id' => $data['driver_id']
        ];

        error_log("Executing booking insert with params: " . print_r($bookingParams, true));
        $stmt->execute($bookingParams);
        $booking_id = $pdo->lastInsertId();
        error_log("Booking inserted successfully with ID: " . $booking_id);
    } catch (PDOException $e) {
        error_log("Database error during booking insert: " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
        error_log("Error Info: " . print_r($stmt->errorInfo(), true));
        throw new Exception("Database error during booking: " . $e->getMessage());
    }

    // Create detailed notification for driver
    $notificationMessage = sprintf(
        "New booking request from %s\nPickup: %s\nDrop-off: %s\nSeats: %s\nTotal Fare: ₱%s",
        $passenger['fullname'],
        $data['pickup'],
        $data['dropoff'],
        $data['seats'],
        $data['total_fare']
    );

    $stmt = $pdo->prepare("
        INSERT INTO notifications (
            user_type,
            driver_id,
            passenger_id,
            type,
            message,
            status,
            created_at
        ) VALUES (
            'driver',
            :driver_id,
            NULL,
            'New Booking',
            :message,
            'unread',
            NOW()
        )
    ");

    $notificationParams = [
        'driver_id' => $data['driver_id'],
        'message' => $notificationMessage
    ];

    error_log("Executing driver notification insert with params: " . print_r($notificationParams, true));
    $stmt->execute($notificationParams);

    // Create notification for passenger
    $passengerMessage = sprintf(
        "Your booking has been confirmed!\nDriver: %s\nPickup: %s\nDrop-off: %s\nSeats: %s\nTotal Fare: ₱%s",
        $driver['fullname'],
        $data['pickup'],
        $data['dropoff'],
        $data['seats'],
        $data['total_fare']
    );

    $stmt = $pdo->prepare("
        INSERT INTO notifications (
            user_type,
            driver_id,
            passenger_id,
            type,
            message,
            status,
            created_at
        ) VALUES (
            'passenger',
            NULL,
            :passenger_id,
            'Booking Confirmed',
            :message,
            'unread',
            NOW()
        )
    ");

    $notificationParams = [
        'passenger_id' => $data['passenger_id'],
        'message' => $passengerMessage
    ];

    error_log("Executing passenger notification insert with params: " . print_r($notificationParams, true));
    $stmt->execute($notificationParams);

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Booking successful',
        'booking_id' => $booking_id,
        'driver_name' => $driver['fullname']
    ]);

} catch (Exception $e) {
    // Rollback transaction if started
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Booking error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your booking: ' . $e->getMessage()
    ]);
} 