<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

// Fetch live driver and passenger locations
try {
    $drivers = [];
    $passengers = [];
    $result = $conn->query("SELECT latitude as lat, longitude as lng FROM driver_locations WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
    while ($row = $result->fetch_assoc()) {
        $drivers[] = $row;
    }
    $result2 = $conn->query("SELECT latitude as lat, longitude as lng FROM passenger_locations WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
    while ($row = $result2->fetch_assoc()) {
        $passengers[] = $row;
    }
    echo json_encode(['success' => true, 'drivers' => $drivers, 'passengers' => $passengers]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 