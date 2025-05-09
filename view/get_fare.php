<?php
$conn = new mysqli("localhost", "root", "", "user_auth");
if ($conn->connect_error) {
    die("Connection failed");
}

$pickup = $_POST['pickup'] ?? '';
$destination = $_POST['destination'] ?? '';

if ($pickup && $destination) {
    $stmt = $conn->prepare("SELECT fare FROM fare_matrix WHERE origin = ? AND destination = ?");
    $stmt->bind_param("ss", $pickup, $destination);
    $stmt->execute();
    $stmt->bind_result($fare);
    
    if ($stmt->fetch()) {
        echo $fare;
    } else {
        echo "0";
    }

    $stmt->close();
} else {
    echo "0";
}
?>
