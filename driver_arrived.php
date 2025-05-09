<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['booking_id']) && isset($data['passenger_id'])) {
        try {
            // Store arrival state in session with additional data
            $_SESSION['arrival_state'] = [
                'booking_id' => $data['booking_id'],
                'passenger_id' => $data['passenger_id'],
                'timestamp' => time(),
                'is_active' => true
            ];
            
            // Ensure session is written
            session_write_close();
            
            // Get passenger info for notification
            $stmt = $pdo->prepare("SELECT p.*, b.destination FROM passenger p 
                                 JOIN bookings b ON p.passenger_id = b.passenger_id 
                                 WHERE b.id = :booking_id");
            $stmt->execute(['booking_id' => $data['booking_id']]);
            $passenger = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($passenger) {
                $destination = $passenger['destination'];
                $message = "You have arrived safely at your set drop-off point ($destination). Thank you for riding with us!";

                // Insert notification for passenger
                $stmt = $pdo->prepare("INSERT INTO notifications (user_type, passenger_id, booking_id, type, message, status, created_at) 
                                     VALUES ('passenger', :passenger_id, :booking_id, 'Driver Arrived', :message, 'unread', NOW())");
                $stmt->execute([
                    'passenger_id' => $data['passenger_id'],
                    'booking_id' => $data['booking_id'],
                    'message' => $message
                ]);
            }
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 