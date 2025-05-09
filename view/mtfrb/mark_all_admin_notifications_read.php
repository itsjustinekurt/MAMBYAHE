<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE user_type = 'admin' AND status = 'unread' AND (type LIKE '%complaint%' OR type LIKE '%report%')");
    $stmt->execute();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 