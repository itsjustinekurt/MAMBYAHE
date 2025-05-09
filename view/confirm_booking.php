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

    if (!$data || !isset($data['booking_id']) || !isset($data['passenger_id'])) {
        throw new Exception('Missing required data: booking_id and passenger_id are required');
    }

    // Validate booking_id and passenger_id are numeric
    if (!is_numeric($data['booking_id']) || !is_numeric($data['passenger_id'])) {
        throw new Exception('Invalid booking_id or passenger_id format');
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
            SELECT id, status 
            FROM bookings 
            WHERE id = :booking_id 
            AND passenger_id = :passenger_id
        ");
        
        $checkStmt->execute([
            'booking_id' => $data['booking_id'],
            'passenger_id' => $data['passenger_id']
        ]);
        
        $booking = $checkStmt->fetch();
        
        if (!$booking) {
            throw new Exception('Booking not found');
        }
        
        // Check if status exists and is not empty
        if (!isset($booking['status']) || empty($booking['status'])) {
            throw new Exception('Invalid!');
        }
        
        // Handle different booking statuses
        switch ($booking['status']) {
            case 'pending':
                // Proceed with confirmation
                break;
            case 'confirmed':
                // If already confirmed but not arrived, show "on the go" message
                echo json_encode([
                    'success' => false,
                    'message' => 'Booking is already confirmed and on the go',
                    'status' => 'confirmed'
                ]);
                exit;
            case 'arrived':
                // If already arrived, redirect to ride history
                echo json_encode([
                    'success' => false,
                    'message' => 'Booking has already been completed',
                    'status' => 'arrived',
                    'redirect' => 'ride_history.php'
                ]);
                exit;
            case 'completed':
                // If completed, redirect to ride history
                echo json_encode([
                    'success' => false,
                    'message' => 'Booking has already been completed',
                    'status' => 'completed',
                    'redirect' => 'ride_history.php'
                ]);
                exit;
            default:
                throw new Exception('Invalid booking status: ' . $booking['status']);
        }

        // Update booking status to 'confirmed'
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET status = 'confirmed', 
                driver_id = :driver_id
            WHERE id = :booking_id 
            AND passenger_id = :passenger_id
            AND status = 'pending'
        ");

        $stmt->execute([
            'driver_id' => $_SESSION['driver_id'],
            'booking_id' => $data['booking_id'],
            'passenger_id' => $data['passenger_id']
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception('Failed to update booking status');
        }

        // Create notification for passenger
        $stmt = $pdo->prepare("
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
                'booking_confirmed',
                'Your ride request has been confirmed by the driver',
                'unread',
                'passenger',
                NOW()
            )
        ");

        $stmt->execute([
            'booking_id' => $data['booking_id'],
            'passenger_id' => $data['passenger_id'],
            'driver_id' => $_SESSION['driver_id']
        ]);

        // Commit transaction
        $pdo->commit();

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Booking confirmed successfully'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    // Log database error with more details
    error_log("Database error in confirm_booking.php: " . $e->getMessage());
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
    error_log("Error in confirm_booking.php: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
