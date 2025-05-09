<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Get JSON data from request
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['booking_id']) || !isset($data['passenger_id'])) {
        throw new Exception('Missing required parameters');
    }

    $booking_id = $data['booking_id'];
    $passenger_id = $data['passenger_id'];

    // Update booking status to 'arrived'
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'arrived' WHERE id = :booking_id");
    $stmt->execute(['booking_id' => $booking_id]);

    // Create notification for passenger
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, user_type, type, message, booking_id, status) 
                          VALUES (:passenger_id, 'passenger', 'arrival', 'Your driver has arrived at the pickup location', :booking_id, 'unread')");
    $stmt->execute([
        'passenger_id' => $passenger_id,
        'booking_id' => $booking_id
    ]);

    echo json_encode(['success' => true, 'message' => 'Arrival notification sent successfully']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 