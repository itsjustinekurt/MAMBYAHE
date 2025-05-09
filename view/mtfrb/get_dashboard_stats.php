<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

try {
    // Get total associations
    $stmt = $pdo->query("SELECT COUNT(*) FROM association");
    $associations = $stmt->fetchColumn();

    // Get total drivers
    $stmt = $pdo->query("SELECT COUNT(*) FROM driver");
    $drivers = $stmt->fetchColumn();

    // Get total passengers
    $stmt = $pdo->query("SELECT COUNT(*) FROM passenger");
    $passengers = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'associations' => $associations,
        'drivers' => $drivers,
        'passengers' => $passengers
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 