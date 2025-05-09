<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['driver_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$booking_id = $data['booking_id'] ?? null;

if (!$booking_id) {
    echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT b.*, p.fullname, p.profile_pic, p.phone as passenger_phone
        FROM bookings b
        LEFT JOIN passenger p ON b.passenger_id = p.passenger_id
        WHERE b.id = :booking_id
    ");
    
    $stmt->execute(['booking_id' => $booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }
    
    // Format the response
    $response = [
        'success' => true,
        'booking_id' => $booking['id'],
        'passenger_id' => $booking['passenger_id'],
        'fullname' => $booking['fullname'] ?? 'Unknown',
        'profile_pic' => $booking['profile_pic'] ?? '',
        'passenger_phone' => $booking['passenger_phone'] ?? 'Not available',
        'pickup_location' => $booking['pickup'] ?? 'Not specified',
        'destination' => $booking['destination'] ?? 'Not specified',
        'seats' => $booking['seats'] ?? 0,
        'fare' => $booking['fare'] ?? 0,
        'status' => $booking['status'] ?? 'pending'
    ];
    
    echo json_encode($response);
} catch (PDOException $e) {
    error_log("Error getting booking details: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?> 