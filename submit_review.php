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
        // Start transaction
        $pdo->beginTransaction();

        // Insert review
        $stmt = $pdo->prepare("INSERT INTO reviews (booking_id, driver_id, rating, feedback, created_at) 
                              VALUES (:booking_id, :driver_id, :rating, :feedback, NOW())");
        $stmt->execute([
            'booking_id' => $data['booking_id'],
            'driver_id' => $data['driver_id'],
            'rating' => $data['rating'],
            'feedback' => $data['feedback']
        ]);

        // If there's a report, insert it
        if ($data['report_issue'] && !empty($data['report_details'])) {
            $stmt = $pdo->prepare("INSERT INTO reports (booking_id, driver_id, report_details, status, created_at) 
                                  VALUES (:booking_id, :driver_id, :report_details, 'pending', NOW())");
            $stmt->execute([
                'booking_id' => $data['booking_id'],
                'driver_id' => $data['driver_id'],
                'report_details' => $data['report_details']
            ]);
        }

        // Update booking status to 'completed'
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'completed' WHERE id = :booking_id");
        $stmt->execute(['booking_id' => $data['booking_id']]);

        // Create notification for driver about the review
        $stmt = $pdo->prepare("INSERT INTO notifications (user_type, driver_id, booking_id, type, message, status, created_at) 
                              VALUES ('driver', :driver_id, :booking_id, 'Review Received', 'You have received a new review for your ride', 'unread', NOW())");
        $stmt->execute([
            'driver_id' => $data['driver_id'],
            'booking_id' => $data['booking_id']
        ]);

        // Commit transaction
        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 