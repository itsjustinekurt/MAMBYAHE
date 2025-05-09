<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['passenger_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

try {
    // Fetch available drivers (those who are approved, online and not currently on a trip)
    $stmt = $pdo->prepare("
        SELECT d.driver_id, d.fullname, d.plate_no, d.profile_pic
        FROM driver d
        WHERE d.status = 'approved'  -- Check if driver is approved
        AND d.is_online = 'online'   -- Check if driver is online
        AND d.driver_id NOT IN (
            SELECT b.driver_id 
            FROM bookings b 
            WHERE b.status IN ('pending', 'accepted', 'in_progress')
        )
        ORDER BY d.fullname ASC
    ");
    
    $stmt->execute();
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log the number of available drivers for debugging
    error_log("Number of available drivers found: " . count($drivers));

    echo json_encode([
        'success' => true,
        'drivers' => $drivers
    ]);

} catch (PDOException $e) {
    error_log("Database error in get_available_drivers.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 