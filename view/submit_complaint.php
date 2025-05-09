<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON
header('Content-Type: application/json');

// Check if driver is logged in
if (!isset($_SESSION['driver_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Driver not logged in'
    ]);
    exit;
}

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
if (!isset($data['booking_id']) || !isset($data['passenger_id']) || !isset($data['driver_id']) || 
    !isset($data['reason']) || !isset($data['details'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

// Database credentials
$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Insert complaint into database
    $stmt = $pdo->prepare("
        INSERT INTO complaints (
            booking_id,
            passenger_id,
            driver_id,
            reason,
            details,
            status,
            created_at
        ) VALUES (
            :booking_id,
            :passenger_id,
            :driver_id,
            :reason,
            :details,
            'pending',
            NOW()
        )
    ");
    
    $stmt->execute([
        'booking_id' => $data['booking_id'],
        'passenger_id' => $data['passenger_id'],
        'driver_id' => $data['driver_id'],
        'reason' => $data['reason'],
        'details' => $data['details']
    ]);
    
    // Update booking status to indicate complaint
    $stmt = $pdo->prepare("
        UPDATE bookings 
        SET has_complaint = 1 
        WHERE id = :booking_id
    ");
    
    $stmt->execute([
        'booking_id' => $data['booking_id']
    ]);
    
    // Create notification for admin
    $stmt = $pdo->prepare("
        INSERT INTO notifications (
            user_type,
            type,
            message,
            booking_id,
            passenger_id,
            driver_id,
            status,
            created_at
        ) VALUES (
            'admin',
            'New Complaint',
            :message,
            :booking_id,
            :passenger_id,
            :driver_id,
            'unread',
            NOW()
        )
    ");
    
    $message = "New complaint submitted by driver for booking #" . $data['booking_id'];
    
    $stmt->execute([
        'message' => $message,
        'booking_id' => $data['booking_id'],
        'passenger_id' => $data['passenger_id'],
        'driver_id' => $data['driver_id']
    ]);
    
    // Commit transaction
    $pdo->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Complaint submitted successfully'
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log error
    error_log("Database error in submit_complaint.php: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
    
} catch (Exception $e) {
    // Log error
    error_log("General error in submit_complaint.php: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request'
    ]);
}
?> 