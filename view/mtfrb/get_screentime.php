<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$association_id = isset($_GET['association_id']) ? intval($_GET['association_id']) : 0;
$range = isset($_GET['range']) ? $_GET['range'] : 'this_week';

// Determine date range
switch ($range) {
    case 'week':
        $date_filter = 's.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
        break;
    case 'month':
        $date_filter = 's.date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)';
        break;
    case 'year':
        $date_filter = 's.date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)';
        break;
    case 'this_week':
    default:
        $date_filter = 'YEARWEEK(s.date, 1) = YEARWEEK(CURDATE(), 1)';
        break;
}

$labels = [];
$values = [];

if ($association_id > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT d.fullname, COALESCE(SUM(s.minutes_online), 0) as total_minutes
            FROM driver d
            LEFT JOIN screentime s ON d.driver_id = s.driver_id AND $date_filter
            WHERE d.association_id = :association_id
            GROUP BY d.driver_id, d.fullname
            ORDER BY total_minutes DESC
        ");
        $stmt->execute(['association_id' => $association_id]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $labels[] = $row['fullname'];
            $values[] = (int)$row['total_minutes'];
        }

        echo json_encode([
            'success' => true,
            'labels' => $labels,
            'values' => $values
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No association selected'
    ]);
} 