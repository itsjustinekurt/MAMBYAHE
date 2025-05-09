<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['driver_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['status']) || !in_array($data['status'], ['online', 'offline'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
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

    // Update driver's online status
    $stmt = $pdo->prepare("
        UPDATE driver 
        SET is_online = :is_online 
        WHERE driver_id = :driver_id
    ");

    $stmt->execute([
        'is_online' => $data['status'],
        'driver_id' => $_SESSION['driver_id']
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 