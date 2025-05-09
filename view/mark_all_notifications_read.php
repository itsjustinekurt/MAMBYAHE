<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['passenger_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

try {
    // Mark all notifications as read
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET status = 'read' 
        WHERE passenger_id = :passenger_id 
        AND user_type = 'passenger'
        AND status = 'unread'
    ");
    
    $stmt->execute(['passenger_id' => $_SESSION['passenger_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'All notifications marked as read'
    ]);

} catch (PDOException $e) {
    error_log("Database error in mark_all_notifications_read.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 