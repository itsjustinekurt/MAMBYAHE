<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['passenger_id'], $data['driver_id'], $data['booking_id'], $data['reason'], $data['details'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit();
    }
    $passenger_id = (int)$data['passenger_id'];
    $driver_id = (int)$data['driver_id'];
    $booking_id = (int)$data['booking_id'];
    $reason = trim($data['reason']);
    $details = trim($data['details']);
    if ($reason === '' || $details === '') {
        echo json_encode(['success' => false, 'message' => 'Reason and details are required']);
        exit();
    }
    $stmt = $pdo->prepare("INSERT INTO passenger_complaints (passenger_id, driver_id, booking_id, reason, details) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$passenger_id, $driver_id, $booking_id, $reason, $details]);
    echo json_encode(['success' => true, 'message' => 'Complaint submitted successfully and will be reviewed by MTFRB.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 