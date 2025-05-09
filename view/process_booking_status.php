<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['driver_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['booking_id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Update booking status
    $stmt = $pdo->prepare("
        UPDATE bookings 
        SET status = :status,
            updated_at = NOW()
        WHERE id = :booking_id 
        AND driver_id = :driver_id
    ");

    $stmt->execute([
        'status' => $data['status'],
        'booking_id' => $data['booking_id'],
        'driver_id' => $_SESSION['driver_id']
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Booking not found or unauthorized');
    }

    // Get booking details for notification
    $stmt = $pdo->prepare("
        SELECT b.*, p.passenger_id, p.username as passenger_username,
               d.fullname as driver_name
        FROM bookings b
        JOIN passenger p ON b.passenger_id = p.passenger_id
        JOIN driver d ON b.driver_id = d.driver_id
        WHERE b.id = :booking_id
    ");
    $stmt->execute(['booking_id' => $data['booking_id']]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    // Create notification for passenger
    $notificationType = '';
    $message = '';
    
    switch ($data['status']) {
        case 'accepted':
            $notificationType = 'Booking Accepted';
            $message = sprintf(
                "Your booking has been accepted by %s\nPickup: %s\nDestination: %s\nSeats: %d\nTotal Fare: ₱%.2f",
                $booking['driver_name'],
                $booking['pickup'],
                $booking['destination'],
                $booking['seats'],
                $booking['fare']
            );
            break;
            
        case 'in_progress':
            $notificationType = 'Driver Arrived';
            $message = sprintf(
                "Driver %s has arrived at your pickup location\nPickup: %s\nDestination: %s\nClick here to rate your experience",
                $booking['driver_name'],
                $booking['pickup'],
                $booking['destination']
            );
            break;
            
        case 'completed':
            $notificationType = 'Trip Completed';
            $message = sprintf(
                "Your trip with %s has been completed\nPickup: %s\nDestination: %s\nTotal Fare: ₱%.2f",
                $booking['driver_name'],
                $booking['pickup'],
                $booking['destination'],
                $booking['fare']
            );
            break;
            
        case 'cancelled':
            $notificationType = 'Booking Cancelled';
            $message = sprintf(
                "Your booking has been cancelled by %s\nPickup: %s\nDestination: %s",
                $booking['driver_name'],
                $booking['pickup'],
                $booking['destination']
            );
            break;
    }

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
            :type,
            :message,
            'unread',
            'passenger',
            NOW()
        )
    ");

    $stmt->execute([
        'passenger_id' => $booking['passenger_id'],
        'driver_id' => $_SESSION['driver_id'],
        'booking_id' => $data['booking_id'],
        'type' => $notificationType,
        'message' => $message
    ]);

    $pdo->commit();

    // Return success response with additional data for UI updates
    echo json_encode([
        'success' => true,
        'message' => 'Booking status updated successfully',
        'status' => $data['status'],
        'booking' => $booking,
        'showBottomSheet' => $data['status'] === 'accepted',
        'showReviewModal' => $data['status'] === 'in_progress'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error in process_booking_status.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 