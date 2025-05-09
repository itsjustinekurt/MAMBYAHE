<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (
    !isset($data['role']) || $data['role'] !== 'passenger' ||
    empty($data['username']) || empty($data['email']) || empty($data['newPassword'])
) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$username = $data['username'];
$email = $data['email'];
$newPassword = $data['newPassword'];

try {
    $stmt = $pdo->prepare("SELECT * FROM passenger WHERE username = ? AND email = ?");
    $stmt->execute([$username, $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Username and email do not match.']);
        exit;
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE passenger SET password = ? WHERE passenger_id = ?");
    $stmt->execute([$hashedPassword, $user['passenger_id']]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
