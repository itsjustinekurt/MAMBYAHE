<?php
// store_booking.php
require_once 'connection.php'; // Include your database connection script

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve POST data
    $pickup = $_POST['pickup'];
    $dropoff = $_POST['dropoff'];
    $seats = $_POST['seats'];
    $children = $_POST['children'] ?? 0;
    $adults = $_POST['adults'] ?? 0;
    $citizens = $_POST['citizens'] ?? 0;
    $datetime = $_POST['datetime'];
    $driver_id = $_POST['driver'];

    // Assuming you have a session with the passenger's name
    session_start();
    $passenger_name = $_SESSION['passenger_name'] ?? 'Unknown'; // Replace with the correct way to get passenger's name

    // Calculate fare based on pickup, dropoff, and seats
    $fare = calculateFare($pickup, $dropoff, $seats);

    // Insert the booking details into the database
    $query = "INSERT INTO ride_details (passenger_name, pickup, dropoff, seats, children, adults, citizens, fare, datetime, driver_id) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssiiiidsi', $passenger_name, $pickup, $dropoff, $seats, $children, $adults, $citizens, $fare, $datetime, $driver_id);

    if ($stmt->execute()) {
        // Respond with success message
        echo json_encode(['success' => true]);
    } else {
        // Respond with an error message if the query fails
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
}

// Calculate fare function
function calculateFare($pickup, $dropoff, $seats) {
    global $conn; // Use the global $conn variable for database connection

    // Query the fare matrix for the selected pickup and dropoff locations
    $query = "SELECT fare FROM fare_matrix WHERE origin = ? AND destination = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $pickup, $dropoff);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Multiply the fare by the number of seats
        return $row['fare'] * $seats;
    } else {
        return 0; // Return 0 if no matching fare is found
    }
}
?>
