<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT a.id, a.name, 
            COALESCE(SUM(CASE 
                WHEN DATE(t.timestamp) = CURDATE() THEN 
                    TIMESTAMPDIFF(SECOND, t.start_time, t.end_time) 
                END), 0) AS today_time,
            COALESCE(SUM(CASE 
                WHEN DATE(t.timestamp) BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE() THEN 
                    TIMESTAMPDIFF(SECOND, t.start_time, t.end_time) 
                END), 0) AS week_time
            FROM associations a
            LEFT JOIN screen_time_tracking t ON a.id = t.association_id
            GROUP BY a.id, a.name
            ORDER BY today_time DESC";
    
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    
    $associations = [];
    $labels = [];
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $associations[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'today_time' => convertSecondsToHoursMinutes($row['today_time']),
            'week_time' => convertSecondsToHoursMinutes($row['week_time'])
        ];
        $labels[] = $row['name'];
        $data[] = $row['today_time']; // In seconds for chart
    }
    
    echo json_encode([
        'labels' => $labels,
        'data' => $data,
        'associations' => $associations
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function convertSecondsToHoursMinutes($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    return sprintf("%02d:%02d", $hours, $minutes);
}
?>
