<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (isset($_GET['passenger_id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, b.pickup_location, b.destination
            FROM passenger p
            LEFT JOIN bookings b ON b.passenger_id = p.passenger_id
            WHERE p.passenger_id = :passenger_id
            ORDER BY b.created_at DESC
            LIMIT 1
        ");
        
        $stmt->execute(['passenger_id' => $_GET['passenger_id']]);
        $passenger = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($passenger) {
            echo json_encode([
                'success' => true,
                'passenger_name' => $passenger['fullname'],
                'profile_pic' => $passenger['profile_pic'] ? 'uploads/' . $passenger['profile_pic'] : null,
                'phone' => $passenger['phone'],
                'pickup_location' => $passenger['pickup_location'],
                'destination' => $passenger['destination']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Passenger not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing passenger ID']);
} 