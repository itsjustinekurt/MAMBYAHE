<?php
include '../connection.php';
session_start();

$booking_id = $_POST['booking_id'];
$response = $_POST['response'];
$driver_id = $_SESSION['driver_id'];

$stmt = $conn->prepare("UPDATE bookings SET status = ?, driver_response_time = NOW() WHERE id = ? AND driver_id = ?");
$stmt->bind_param("sii", $response, $booking_id, $driver_id);
$stmt->execute();

// Get passenger_id for notification
$get_passenger = $conn->prepare("SELECT passenger_id FROM bookings WHERE id = ?");
$get_passenger->bind_param("i", $booking_id);
$get_passenger->execute();
$get_passenger->bind_result($passenger_id);
$get_passenger->fetch();
$get_passenger->close();

$msg = "Your booking has been " . strtolower($response) . " by the driver.";

$notify = $conn->prepare("INSERT INTO notifications (user_id, role, message) VALUES (?, 'passenger', ?)");
$notify->bind_param("is", $passenger_id, $msg);
$notify->execute();

echo "Booking $response successfully.";
