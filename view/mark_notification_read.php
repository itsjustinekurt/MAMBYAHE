<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['driver_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$notification_id = $data['notification_id'] ?? null;

if (!$notification_id) {
    echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET status = 'read' 
        WHERE notification_id = :notification_id 
        AND driver_id = :driver_id
    ");
    
    $stmt->execute([
        'notification_id' => $notification_id,
        'driver_id' => $_SESSION['driver_id']
    ]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log("Error marking notification as read: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>