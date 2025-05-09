<?php
require_once '../db_connect.php';
header('Content-Type: application/json');
try {
    $total = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'completed'")->fetchColumn();
    $stmt = $pdo->query("SELECT destination AS name, COUNT(*) AS cnt FROM bookings WHERE status = 'completed' GROUP BY destination ORDER BY cnt DESC LIMIT 3");
    $result = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $percent = $total > 0 ? round(($row['cnt'] / $total) * 100) : 0;
        $result[] = [
            'name' => $row['name'],
            'percent' => $percent
        ];
    }
    echo json_encode($result);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 