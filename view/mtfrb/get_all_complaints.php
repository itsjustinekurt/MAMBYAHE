<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

try {
    $sql = "SELECT c.complaint_id, 
                   IF(c.complainant_type='passenger', p.fullname, d1.fullname) AS complainant_name,
                   c.complainant_type,
                   IF(c.against_type='passenger', p2.fullname, d2.fullname) AS against_name,
                   c.against_type,
                   c.reason, c.details, c.status, c.created_at
            FROM complaints c
            LEFT JOIN passenger p ON c.complainant_type='passenger' AND c.complainant_id = p.passenger_id
            LEFT JOIN driver d1 ON c.complainant_type='driver' AND c.complainant_id = d1.driver_id
            LEFT JOIN passenger p2 ON c.against_type='passenger' AND c.against_id = p2.passenger_id
            LEFT JOIN driver d2 ON c.against_type='driver' AND c.against_id = d2.driver_id
            ORDER BY c.created_at DESC";
    $result = $conn->query($sql);
    $complaints = [];
    while ($row = $result->fetch_assoc()) {
        $complaints[] = $row;
    }
    echo json_encode(['success' => true, 'complaints' => $complaints]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 