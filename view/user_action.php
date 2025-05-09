<?php
header('Content-Type: application/json');
$host = 'localhost';
$db = 'user_auth';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_POST['user_id'] ?? null;
        $action = $_POST['action'] ?? null;
        $suspend_duration = $_POST['suspend_duration'] ?? null;

        if (!$user_id || !$action) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
            exit;
        }

        $message = '';
        if ($action === 'warning') {
            $message = 'You have received a warning from MTFRB.';
        } elseif ($action === 'suspend') {
            $message = "You are suspended for $suspend_duration days.";

            // Store suspension info (if you have a suspension table or column, add logic here)
        } elseif ($action === 'block') {
            $message = 'Your account has been blocked by MTFRB.';

            // You can update driver/passenger status to 'blocked' if needed
            // Try to update both tables in case the user is a driver or passenger
            $stmt1 = $pdo->prepare("UPDATE driver SET status = 'blocked' WHERE driver_id = ?");
            $stmt2 = $pdo->prepare("UPDATE passenger SET status = 'blocked' WHERE passenger_id = ?");
            $stmt1->execute([$user_id]);
            $stmt2->execute([$user_id]);
        }

        // Insert notification (for restriction actions)
        $receiver = null;
        // Try to get the user's full name from driver or passenger
        $stmtName = $pdo->prepare("SELECT fullname FROM driver WHERE driver_id = ?");
        $stmtName->execute([$user_id]);
        $receiver = $stmtName->fetchColumn();
        if (!$receiver) {
            $stmtName = $pdo->prepare("SELECT fullname FROM passenger WHERE passenger_id = ?");
            $stmtName->execute([$user_id]);
            $receiver = $stmtName->fetchColumn();
        }
        $pdo->prepare("INSERT INTO notifications (type, receiver, driver_id, passenger_id, user_id, message, status, created_at) VALUES ('mtfrb', ?, NULLIF((SELECT driver_id FROM driver WHERE driver_id = ?),''), NULLIF((SELECT passenger_id FROM passenger WHERE passenger_id = ?),''), ?, ?, 'unread', NOW())")
            ->execute([$receiver ?: 'User', $user_id, $user_id, $user_id, $message]);

        echo json_encode(['status' => 'success', 'message' => 'Action applied successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
