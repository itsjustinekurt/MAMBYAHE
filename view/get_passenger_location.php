<?php
// Disable error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

// Set content type to JSON
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if booking_id is provided
if (!isset($_GET['booking_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Booking ID is required'
    ]);
    exit;
}

try {
    // Database connection
    $pdo = new PDO(
        "mysql:host=localhost;dbname=user_auth",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $booking_id = $_GET['booking_id'];

    // Get passenger location from the booking
    $stmt = $pdo->prepare("
        SELECT p.latitude, p.longitude
        FROM passenger_locations p
        INNER JOIN bookings b ON b.passenger_id = p.passenger_id
        WHERE b.id = :booking_id
        ORDER BY p.updated_at DESC
        LIMIT 1
    ");
    
    $stmt->execute(['booking_id' => $booking_id]);
    $location = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($location) {
        echo json_encode([
            'success' => true,
            'lat' => $location['latitude'],
            'lng' => $location['longitude']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Passenger location not found'
        ]);
    }

} catch (Exception $e) {
    // Log error but don't expose it to client
    error_log("Error in get_passenger_location.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching passenger location'
    ]);
} 