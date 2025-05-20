<?php
session_start();
require_once '../db_connect.php';

// Get parameters
$todaName = $_GET['todaName'] ?? '';
$range = $_GET['range'] ?? 'today';

header('Content-Type: application/json');

if (!$todaName) {
    http_response_code(400);
    echo json_encode(['error' => 'TODA name is required']);
    exit;
}

try {
    // Get association IDs for the selected TODA
    $sql = "SELECT id FROM associations WHERE toda_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $todaName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $assocIds = array_column($result->fetch_all(MYSQLI_ASSOC), 'id');
    
    if (empty($assocIds)) {
        echo json_encode(['labels' => [], 'values' => []]);
        exit;
    }
    
    // Prepare time range query
    $timeCondition = '';
    switch ($range) {
        case 'today':
            $timeCondition = "DATE(timestamp) = CURDATE()";
            break;
        case 'week':
            $timeCondition = "DATE(timestamp) BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()";
            break;
        case 'month':
            $timeCondition = "DATE(timestamp) BETWEEN DATE_SUB(CURDATE(), INTERVAL 29 DAY) AND CURDATE()";
            break;
    }
    
    // Get screen time data
    $sql = "SELECT 
            DATE(timestamp) as date,
            SUM(TIMESTAMPDIFF(SECOND, start_time, end_time)) as total_time
            FROM screen_time_tracking
            WHERE association_id IN (" . implode(',', $assocIds) . ")
            AND $timeCondition
            GROUP BY DATE(timestamp)
            ORDER BY DATE(timestamp)";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'date' => date('M d', strtotime($row['date'])),
            'time' => round($row['total_time'] / 3600, 1) // Convert seconds to hours
        ];
    }
    
    // Prepare response
    $response = [
        'labels' => array_column($data, 'date'),
        'values' => array_column($data, 'time')
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
