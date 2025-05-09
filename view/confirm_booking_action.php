<?php
$conn = new mysqli("localhost", "root", "", "user_auth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get confirmation data
$pickup = $_POST['pickup'];
$destination = $_POST['destination'];
$seats = $_POST['seats'];
$date = $_POST['date'];
$time = $_POST['time'];
$driver_name = $_POST['driver_name'];

// Insert confirmed data into scheduled_rides table
$sql = "INSERT INTO scheduled_rides (pickup, destination, seats, date, time, driver_name) 
        VALUES ('$pickup', '$destination', '$seats', '$date', '$time', '$driver_name')";

if ($conn->query($sql) === TRUE) {
    echo "Your ride has been successfully confirmed!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
