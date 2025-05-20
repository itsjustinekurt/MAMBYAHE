<?php
session_start();
require_once '../db_connect.php';
header('Content-Type: application/json');

try {
    // Get all active drivers with their current locations
    $sql = "SELECT 
        d.driver_id,
        d.fullname,
        dl.latitude,
        dl.longitude,
        dl.updated_at
    FROM driver d
    LEFT JOIN driver_location dl ON d.driver_id = dl.driver_id
    WHERE dl.updated_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
    ORDER BY dl.updated_at DESC";

    $result = $conn->query($sql);
    $drivers = [];
    
    while ($row = $result->fetch_assoc()) {
        $drivers[] = $row;
    }

    echo json_encode([
        'success' => true,
        'drivers' => $drivers
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching driver locations: ' . $e->getMessage()
    ]);
}
