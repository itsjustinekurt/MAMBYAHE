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

// Check if passenger is logged in
if (!isset($_SESSION['passenger_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Passenger not logged in'
    ]);
    exit;
}

// Database credentials
require_once '../config/database.php';

// Log connection attempt
error_log("Attempting to connect to database");

try {
    // Create PDO connection with error mode
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

    $passenger_id = $_SESSION['passenger_id'];
    error_log("Passenger ID from session: " . $passenger_id);

    // Count unread notifications
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as unread_count 
        FROM notifications 
        WHERE passenger_id = :passenger_id 
        AND user_type = 'passenger'
        AND status = 'unread'
    ");
    $stmt->execute(['passenger_id' => $passenger_id]);
    $unread_count = $stmt->fetchColumn();

    // Get notifications with driver details
    $stmt = $pdo->prepare("
        SELECT n.*, d.fullname as driver_name, d.profile_pic as driver_pic, d.plate_no,
               b.status as booking_status, b.pickup, b.destination, b.seats, b.fare,
               b.id as booking_id
        FROM notifications n
        LEFT JOIN bookings b ON n.booking_id = b.id
        LEFT JOIN driver d ON n.driver_id = d.driver_id
        WHERE n.passenger_id = :passenger_id 
        AND n.user_type = 'passenger'
        ORDER BY n.created_at DESC 
        LIMIT 10
    ");

    $stmt->execute(['passenger_id' => $passenger_id]);
    $notifications = $stmt->fetchAll();

    // Log the results
    error_log("Number of notifications found: " . count($notifications));
    error_log("Unread count: " . $unread_count);

    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unread_count
    ]);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
} 