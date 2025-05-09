<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
});
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "$errstr in $errfile on line $errline"]);
    exit();
});

// Database credentials
$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['booking_id']) || !isset($data['passenger_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing booking_id or passenger_id']);
    exit();
}
$booking_id = $data['booking_id'];

// Fetch the correct passenger_id from the bookings table
$stmt = $pdo->prepare('SELECT passenger_id FROM bookings WHERE id = :booking_id');
$stmt->execute(['booking_id' => $booking_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row || empty($row['passenger_id'])) {
    echo json_encode(['success' => false, 'message' => 'Booking not found or passenger_id missing']);
    exit();
}
$passenger_id = $row['passenger_id'];

// Get driver and booking info for message
$stmt = $pdo->prepare("SELECT d.fullname as driver_name, b.destination FROM bookings b JOIN driver d ON b.driver_id = d.driver_id WHERE b.id = :booking_id");
$stmt->execute(['booking_id' => $booking_id]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$info) {
    echo json_encode(['success' => false, 'message' => 'Booking or driver not found']);
    exit();
}
$driver_name = $info['driver_name'];
$destination = $info['destination'];

$message = "You have arrived safely at your set drop-off point ($destination). Thank you for riding with us!";

// Insert notification for passenger
$stmt = $pdo->prepare("INSERT INTO notifications (user_type, passenger_id, booking_id, type, message, status, created_at) VALUES ('passenger', :passenger_id, :booking_id, 'Driver Arrived', :message, 'unread', NOW())");
$stmt->execute([
    'passenger_id' => $passenger_id,
    'booking_id' => $booking_id,
    'message' => $message
]);

echo json_encode(['success' => true, 'message' => 'Passenger notified of arrival']); 