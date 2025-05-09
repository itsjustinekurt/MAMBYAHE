<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['passenger_id'], $_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$passenger_id = $_SESSION['passenger_id'];

// Database credentials
$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

try {
    // Establish PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Mark all notifications as read when the POST request is made
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_read') {
        // Update all unread notifications for the current user
        $stmt = $pdo->prepare("UPDATE notifications SET status = :status WHERE user_id = :user_id AND status = :unread_status");
        $stmt->execute([
            'status' => 'read',
            'user_id' => $passenger_id,
            'unread_status' => 'unread'
        ]);

        // Return success response
        echo json_encode(['status' => 'success']);
        exit();
    }

} catch (PDOException $e) {
    // Handle errors
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit();
}
?>
