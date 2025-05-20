<?php
session_start();
require_once '../db_connect.php';
header('Content-Type: application/json');

try {
    $stats = [
        'success' => true,
        'associations' => 0,
        'drivers' => 0,
        'passengers' => 0
    ];

    // Try to get total associations
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM association");
        $stats['associations'] = $result->fetch_assoc()['count'];
    } catch (Exception $e) {
        // If association table doesn't exist, just use default value
    }

    // Get total drivers
    $result = $conn->query("SELECT COUNT(*) as count FROM driver");
    $stats['drivers'] = $result->fetch_assoc()['count'];

    // Get total passengers
    $result = $conn->query("SELECT COUNT(*) as count FROM passenger");
    $stats['passengers'] = $result->fetch_assoc()['count'];

    echo json_encode($stats);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}