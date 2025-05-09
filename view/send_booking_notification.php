<?php
// Database credentials
$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

function sendBookingNotification($pdo, $booking_id, $status, $driver_id, $rejection_reason = null) {
    try {
        // Get booking details
        $stmt = $pdo->prepare("
            SELECT b.*, p.passenger_id, p.fullname as passenger_name, p.passenger_phone,
                   d.fullname as driver_name, d.profile_pic as driver_pic
            FROM bookings b 
            JOIN passenger p ON b.passenger_name = p.fullname 
            JOIN driver d ON b.driver_id = d.driver_id
            WHERE b.id = :booking_id
        ");
        $stmt->execute(['booking_id' => $booking_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        // Format date and time
        $booking_date = !empty($booking['booking_date']) ? date('F j, Y', strtotime($booking['booking_date'])) : (!empty($booking['created_at']) ? date('F j, Y', strtotime($booking['created_at'])) : 'Not specified');
        $booking_time = !empty($booking['booking_time']) ? date('g:i A', strtotime($booking['booking_time'])) : (!empty($booking['created_at']) ? date('g:i A', strtotime($booking['created_at'])) : 'Not specified');

        if ($status === 'accepted') {
            // Create notification message for acceptance
            $notificationMessage = sprintf(
                "Your booking has been accepted by %s\n\nBooking Details:\nPickup: %s\nDrop-off: %s\nDate: %s\nTime: %s\nSeats: %s\nTotal Fare: ₱%s",
                $booking['driver_name'],
                $booking['pickup'],
                $booking['destination'],
                $booking_date,
                $booking_time,
                $booking['seats'],
                $booking['fare']
            );

            // Create notification for passenger
            $stmt = $pdo->prepare("
                INSERT INTO notifications (
                    user_type,
                    passenger_id,
                    booking_id,
                    type,
                    message,
                    status,
                    created_at
                ) VALUES (
                    'passenger',
                    :passenger_id,
                    :booking_id,
                    'Booking Accepted',
                    :message,
                    'unread',
                    NOW()
                )
            ");

            $stmt->execute([
                'passenger_id' => $booking['passenger_id'],
                'booking_id' => $booking_id,
                'message' => $notificationMessage
            ]);
        } else {
            // Create notification message for rejection
            $notificationMessage = sprintf(
                "Your booking has been cancelled by %s\n\nReason: %s\n\nBooking Details:\nPickup: %s\nDrop-off: %s\nDate: %s\nTime: %s\nSeats: %s\nTotal Fare: ₱%s",
                $booking['driver_name'],
                $rejection_reason,
                $booking['pickup'],
                $booking['destination'],
                $booking_date,
                $booking_time,
                $booking['seats'],
                $booking['fare']
            );

            // Create notification only for passenger
            $stmt = $pdo->prepare("
                INSERT INTO notifications (
                    user_type,
                    passenger_id,
                    booking_id,
                    type,
                    message,
                    status,
                    created_at
                ) VALUES (
                    'passenger',
                    :passenger_id,
                    :booking_id,
                    'Booking Cancelled',
                    :message,
                    'unread',
                    NOW()
                )
            ");

            $stmt->execute([
                'passenger_id' => $booking['passenger_id'],
                'booking_id' => $booking_id,
                'message' => $notificationMessage
            ]);
        }

        return true;
    } catch (Exception $e) {
        error_log("Error sending booking notification: " . $e->getMessage());
        return false;
    }
}

// Test the function
if (isset($_GET['test'])) {
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $result = sendBookingNotification(
            $pdo, 
            $_GET['booking_id'], 
            $_GET['status'], 
            $_GET['driver_id'],
            $_GET['reason'] ?? null
        );
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Notification sent successfully' : 'Failed to send notification'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?> 