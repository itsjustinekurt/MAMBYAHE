<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['passenger_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['booking_id']) || !isset($data['rating']) || !isset($data['feedback'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Insert review
    $stmt = $pdo->prepare("
        INSERT INTO reviews (
            booking_id,
            passenger_id,
            driver_id,
            rating,
            feedback,
            created_at
        ) VALUES (
            :booking_id,
            :passenger_id,
            :driver_id,
            :rating,
            :feedback,
            NOW()
        )
    ");

    $stmt->execute([
        'booking_id' => $data['booking_id'],
        'passenger_id' => $_SESSION['passenger_id'],
        'driver_id' => $data['driver_id'],
        'rating' => $data['rating'],
        'feedback' => $data['feedback']
    ]);

    // Update booking status to reviewed
    $stmt = $pdo->prepare("
        UPDATE bookings 
        SET status = 'reviewed',
            updated_at = NOW()
        WHERE id = :booking_id 
        AND passenger_id = :passenger_id
    ");

    $stmt->execute([
        'booking_id' => $data['booking_id'],
        'passenger_id' => $_SESSION['passenger_id']
    ]);

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
            'New Review',
            :message,
            'unread',
            'driver',
            NOW()
        )
    ");

    $message = sprintf(
        "You received a %d-star review for your recent trip\nFeedback: %s",
        $data['rating'],
        $data['feedback']
    );

    $stmt->execute([
        'passenger_id' => $_SESSION['passenger_id'],
        'driver_id' => $data['driver_id'],
        'booking_id' => $data['booking_id'],
        'message' => $message
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Review submitted successfully'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error in submit_review.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 