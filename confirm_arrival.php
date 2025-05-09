<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['booking_id']) || !isset($data['driver_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }

    try {
        // Update booking status to 'arrival_confirmed'
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'arrival_confirmed' WHERE id = :booking_id");
        $stmt->execute(['booking_id' => $data['booking_id']]);

        // Create notification for driver
        $stmt = $pdo->prepare("INSERT INTO notifications (user_type, driver_id, booking_id, type, message, status, created_at) 
                              VALUES ('driver', :driver_id, :booking_id, 'Arrival Confirmed', 'Passenger has confirmed your arrival', 'unread', NOW())");
        $stmt->execute([
            'driver_id' => $data['driver_id'],
            'booking_id' => $data['booking_id']
        ]);

        echo json_encode(['success' => true, 'message' => 'Arrival confirmed successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 