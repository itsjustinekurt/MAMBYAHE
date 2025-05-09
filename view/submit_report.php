<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['driver_id'], $data['reason'], $data['details'], $data['passenger_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

$driver_id = $data['driver_id'];
$reason = $data['reason'];
$details = $data['details'];
$passenger_id = $data['passenger_id'];
$file_mtfrb = !empty($data['file_mtfrb']) ? 1 : 0;

// Database credentials
$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("INSERT INTO driver_reports (driver_id, passenger_id, reason, details, file_mtfrb, reported_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$driver_id, $passenger_id, $reason, $details, $file_mtfrb]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 