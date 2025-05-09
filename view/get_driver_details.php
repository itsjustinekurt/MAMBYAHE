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

// Check if driver_id is provided
if (!isset($_GET['driver_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Driver ID is required'
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

    // Get driver details
    $stmt = $pdo->prepare("
        SELECT d.fullname, d.profile_pic, v.plate_no
        FROM driver d
        LEFT JOIN vehicle v ON d.driver_id = v.driver_id
        WHERE d.driver_id = :driver_id
    ");
    
    $stmt->execute(['driver_id' => $_GET['driver_id']]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$driver) {
        echo json_encode([
            'success' => false,
            'message' => 'Driver not found'
        ]);
        exit;
    }

    // Return driver details
    echo json_encode([
        'success' => true,
        'driver' => [
            'name' => $driver['fullname'],
            'profile_pic' => $driver['profile_pic'],
            'plate_no' => $driver['plate_no']
        ]
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
?> 