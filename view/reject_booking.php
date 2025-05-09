<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to JSON
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if driver is logged in
if (!isset($_SESSION['driver_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Driver not logged in'
    ]);
    exit;
}

try {
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Log the received data
    error_log("Received data: " . print_r($data, true));

    if (!$data || !isset($data['booking_id']) || !isset($data['passenger_id']) || !isset($data['reason'])) {
        throw new Exception('Missing required data: booking_id, passenger_id, and reason are required');
    }

    // Validate booking_id and passenger_id are numeric
    if (!is_numeric($data['booking_id']) || !is_numeric($data['passenger_id'])) {
        throw new Exception('Invalid booking_id or passenger_id format');
    }

    // Validate reason is not empty
    if (empty(trim($data['reason']))) {
        throw new Exception('Rejection reason cannot be empty');
    }

    // Database credentials
    $db_host = 'localhost';
    $db_name = 'user_auth';
    $db_user = 'root';
    $db_pass = '';

    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Start transaction
    $pdo->beginTransaction();

    try {
        // First check if the booking exists and is in pending status
        $checkStmt = $pdo->prepare("
            SELECT id, status, passenger_id 
            FROM bookings 
            WHERE id = :booking_id
        ");
        
        $checkStmt->execute([
            'booking_id' => $data['booking_id']
        ]);
        
        $booking = $checkStmt->fetch();
        
        // Log the found booking
        error_log("Found booking: " . print_r($booking, true));
        
        if (!$booking) {
            throw new Exception('Booking not found');
        }

        // Verify passenger_id matches
        if ($booking['passenger_id'] != $data['passenger_id']) {
            throw new Exception('Passenger ID mismatch');
        }

        // Log the current status
        error_log("Current booking status: " . $booking['status']);

        // Check if booking is already processed
        if ($booking['status'] !== 'pending') {
            throw new Exception('This booking has already been processed');
        }

        // Update booking status to 'rejected'
        $updateStmt = $pdo->prepare("
            UPDATE bookings 
            SET status = 'rejected',
                rejection_reason = :reason
            WHERE id = :booking_id 
            AND passenger_id = :passenger_id
            AND status = 'pending'
        ");

        $updateParams = [
            'reason' => $data['reason'],
            'booking_id' => $data['booking_id'],
            'passenger_id' => $data['passenger_id']
        ];
        
        // Log the update parameters
        error_log("Update parameters: " . print_r($updateParams, true));
        
        $updateStmt->execute($updateParams);

        $rowsAffected = $updateStmt->rowCount();
        error_log("Rows affected by update: " . $rowsAffected);

        if ($rowsAffected === 0) {
            // Try to get the current status to provide better error message
            $statusCheck = $pdo->prepare("SELECT status FROM bookings WHERE id = :booking_id");
            $statusCheck->execute(['booking_id' => $data['booking_id']]);
            $currentStatus = $statusCheck->fetch(PDO::FETCH_COLUMN);
            throw new Exception('Failed to update booking status. Current status: ' . ($currentStatus ?: 'unknown'));
        }

        // Verify the update was successful
        $verifyStmt = $pdo->prepare("SELECT status FROM bookings WHERE id = :booking_id");
        $verifyStmt->execute(['booking_id' => $data['booking_id']]);
        $newStatus = $verifyStmt->fetch(PDO::FETCH_COLUMN);
        
        if ($newStatus !== 'rejected') {
            throw new Exception('Failed to update booking status to rejected');
        }

        // Create notification for passenger
        $notifStmt = $pdo->prepare("
            INSERT INTO notifications (
                booking_id,
                passenger_id,
                driver_id,
                type,
                message,
                status,
                user_type,
                created_at
            ) VALUES (
                :booking_id,
                :passenger_id,
                :driver_id,
                'booking_rejected',
                :message,
                'unread',
                'passenger',
                NOW()
            )
        ");

        $rejectionMessage = 'Your ride request has been rejected. Reason: ' . $data['reason'];

        $notifStmt->execute([
            'booking_id' => $data['booking_id'],
            'passenger_id' => $data['passenger_id'],
            'driver_id' => $_SESSION['driver_id'],
            'message' => $rejectionMessage
        ]);

        // Verify notification was created
        $notifId = $pdo->lastInsertId();
        if (!$notifId) {
            throw new Exception('Failed to create notification for passenger');
        }

        // Commit transaction
        $pdo->commit();

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Booking rejected successfully',
            'status' => 'rejected',
            'details' => [
                'booking_id' => $data['booking_id'],
                'status' => 'rejected',
                'reason' => $data['reason'],
                'notification_id' => $notifId
            ]
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    // Log database error with more details
    error_log("Database error in reject_booking.php: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    error_log("Error Info: " . print_r($e->errorInfo, true));
    
    // Return error response with more details
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
} catch (Exception $e) {
    // Log general error
    error_log("Error in reject_booking.php: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 