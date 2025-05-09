<?php
session_start();
require_once '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiverType = $_POST['receiverType'] ?? '';
    $toda = $_POST['toda'] ?? '';
    $address = $_POST['address'] ?? '';
    $message = trim($_POST['message'] ?? '');
    $created_at = date('Y-m-d H:i:s');
    $type = 'MTFRB';
    $success = false;
    $errors = [];

    if (!$receiverType || !$message) {
        $errors[] = 'Receiver type and message are required.';
    }

    $recipients = [];
    if ($receiverType === 'passenger') {
        // All passengers, or by address
        $sql = "SELECT passenger_id, fullname FROM passenger";
        $params = [];
        if ($address) {
            $sql .= " WHERE address = ?";
            $params[] = $address;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($recipients as $row) {
            $pdo->prepare("INSERT INTO notifications (user_type, passenger_id, type, message, status, created_at) VALUES ('passenger', ?, ?, ?, 'unread', ?)")
                ->execute([$row['passenger_id'], $type, $message, $created_at]);
        }
        $success = true;
    } elseif ($receiverType === 'driver') {
        // All drivers
        $stmt = $pdo->query("SELECT driver_id, fullname FROM driver");
        $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($recipients as $row) {
            $pdo->prepare("INSERT INTO notifications (user_type, driver_id, type, message, status, created_at) VALUES ('driver', ?, ?, ?, 'unread', ?)")
                ->execute([$row['driver_id'], $type, $message, $created_at]);
        }
        $success = true;
    } elseif ($receiverType === 'association' && $toda) {
        // All drivers in a specific TODA
        $stmt = $pdo->prepare("SELECT driver_id, fullname FROM driver WHERE toda = ?");
        $stmt->execute([$toda]);
        $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($recipients as $row) {
            $pdo->prepare("INSERT INTO notifications (user_type, driver_id, type, message, status, created_at) VALUES ('driver', ?, ?, ?, 'unread', ?)")
                ->execute([$row['driver_id'], $type, $message, $created_at]);
        }
        $success = true;
    } else {
        $errors[] = 'Invalid receiver type or missing filter.';
    }

    if ($success) {
        $_SESSION['notif_success'] = 'Notification sent successfully!';
    } else {
        $_SESSION['notif_error'] = implode(' ', $errors);
    }
}
header('Location: send_notification.php');
exit; 