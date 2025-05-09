<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['driver_id'])) {
    $_SESSION['selected_driver'] = $_POST['driver_id'];
    header("Location: confirm_ride.php"); // Redirect to next step
    exit();
}
?>
