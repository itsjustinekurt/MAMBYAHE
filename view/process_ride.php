<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup = $_POST['pickup'];
    $destination = $_POST['destination'];
    $date = $_POST['date'] ?? 'Today';
    $time = $_POST['time'] ?? 'ASAP';
    $toda = $_POST['toda'];

    // For now, just show data (replace with DB save and driver match logic)
    echo "<h2>Ride Details</h2>";
    echo "Pickup: $pickup<br>";
    echo "Destination: $destination<br>";
    echo "Date: $date<br>";
    echo "Time: $time<br>";
    echo "TODA: $toda<br>";
}
?>
