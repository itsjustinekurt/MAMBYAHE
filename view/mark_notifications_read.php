<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['driver_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Database credentials
$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Mark all notifications as read
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET status = 'read' 
        WHERE driver_id = :driver_id 
        AND user_type = 'driver'
        AND status = 'unread'
    ");

    $stmt->execute(['driver_id' => $_SESSION['driver_id']]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 