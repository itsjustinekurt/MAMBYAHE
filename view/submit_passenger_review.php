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
    $passenger_id = isset($data['passenger_id']) ? (int)$data['passenger_id'] : null;
    $driver_id = isset($data['driver_id']) ? (int)$data['driver_id'] : null;
    $booking_id = isset($data['booking_id']) ? (int)$data['booking_id'] : null;
    $rating = isset($data['rating']) ? (int)$data['rating'] : null;
    $review = isset($data['review']) ? trim($data['review']) : '';

    // If review is provided, rating is required
    if ($review !== '' && ($rating === null || $rating < 1 || $rating > 5)) {
        echo json_encode(['success' => false, 'message' => 'If you provide a review, you must also provide a star rating (1-5).']);
        exit();
    }
    // If neither review nor rating is provided, do not insert
    if ($review === '' && ($rating === null || $rating < 1 || $rating > 5)) {
        echo json_encode(['success' => false, 'message' => 'No review or rating provided.']);
        exit();
    }
    // Insert only if at least rating or review is present
    $stmt = $pdo->prepare("INSERT INTO passenger_reviews (passenger_id, driver_id, booking_id, rating, review) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $passenger_id,
        $driver_id,
        $booking_id,
        $rating,
        $review
    ]);
    echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 