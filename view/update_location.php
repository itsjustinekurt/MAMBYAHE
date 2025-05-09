<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['driver_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['latitude']) || !isset($data['longitude'])) {
    echo json_encode(['success' => false, 'message' => 'Missing location data']);
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

    // Update or insert driver location
    $stmt = $pdo->prepare("
        INSERT INTO driver_locations (driver_id, latitude, longitude, updated_at)
        VALUES (:driver_id, :latitude, :longitude, NOW())
        ON DUPLICATE KEY UPDATE
        latitude = :latitude,
        longitude = :longitude,
        updated_at = NOW()
    ");

    $stmt->execute([
        'driver_id' => $_SESSION['driver_id'],
        'latitude' => $data['latitude'],
        'longitude' => $data['longitude']
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 