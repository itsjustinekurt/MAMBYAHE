<?php
session_start();

$conn = new mysqli("localhost", "root", "", "user_auth");
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

if (!isset($_SESSION['passenger_id']) || !isset($_SESSION['fullname'])) {
    echo json_encode(["error" => "Session expired. Please log in again."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$passenger_name = $_SESSION['fullname'];
$passenger_id = $_SESSION['passenger_id'];

$pickup = $data['pickup'];
$destination = $data['destination'];
$fare = $data['farePerSeat'];
$total_fare = $data['totalFare'];
$seats = $data['seats'];
$pickup_time = $data['pickupTime'];
$driver_id = $data['driverId'];

// Save booking
$stmt = $conn->prepare("INSERT INTO bookings (passenger_id, passenger_name, pickup, destination, fare, total_fare, seats, pickup_time, driver_id, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
$stmt->bind_param("isssddiisi", $passenger_id, $passenger_name, $pickup, $destination, $fare, $total_fare, $seats, $pickup_time, $driver_id);
$stmt->execute();
$booking_id = $stmt->insert_id;

// Get driver details
$driver_stmt = $conn->prepare("SELECT fullname, plate_number FROM driver WHERE id = ?");
$driver_stmt->bind_param("i", $driver_id);
$driver_stmt->execute();
$driver_result = $driver_stmt->get_result();

if ($driver_result->num_rows > 0) {
    $driver = $driver_result->fetch_assoc();
    echo json_encode([
        "booking_id" => $booking_id,
        "passenger_name" => $passenger_name,
        "driver" => $driver["fullname"],
        "plate" => $driver["plate_number"]
    ]);
} else {
    echo json_encode(["error" => "Driver not found"]);
}
?>
