<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$passenger_id = $data['passenger_id'] ?? null;
$booking_id = $data['booking_id'] ?? null;

if (!$passenger_id || !$booking_id) {
    echo json_encode(['success' => false, 'message' => 'Missing passenger_id or booking_id']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO notifications (passenger_id, booking_id, type, message, user_type, status, created_at) VALUES (?, ?, 'Driver Arrived', 'Your driver has arrived at the pickup location.', 'passenger', 'unread', NOW())");
    $stmt->execute([$passenger_id, $booking_id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
