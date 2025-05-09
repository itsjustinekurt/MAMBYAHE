<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to JSON
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if passenger is logged in
if (!isset($_SESSION['passenger_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ]);
    exit;
}

// Get JSON data from request body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
if (!isset($data['latitude']) || !isset($data['longitude'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Latitude and longitude are required'
    ]);
    exit;
}

try {
    // Database connection
    $pdo = new PDO(
        "mysql:host=localhost;dbname=user_auth",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Check if location record exists
    $stmt = $pdo->prepare("
        SELECT id FROM passenger_locations 
        WHERE passenger_id = :passenger_id
    ");
    $stmt->execute(['passenger_id' => $_SESSION['passenger_id']]);
    $location = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($location) {
        // Update existing location
        $stmt = $pdo->prepare("
            UPDATE passenger_locations 
            SET latitude = :latitude, 
                longitude = :longitude, 
                updated_at = CURRENT_TIMESTAMP 
            WHERE passenger_id = :passenger_id
        ");
    } else {
        // Insert new location
        $stmt = $pdo->prepare("
            INSERT INTO passenger_locations 
            (passenger_id, latitude, longitude, created_at, updated_at) 
            VALUES 
            (:passenger_id, :latitude, :longitude, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
    }

    // Execute the query
    $stmt->execute([
        'passenger_id' => $_SESSION['passenger_id'],
        'latitude' => $data['latitude'],
        'longitude' => $data['longitude']
    ]);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Location updated successfully'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
exit; 