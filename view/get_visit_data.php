<?php
include '../view/connection.php';

$sql = "SELECT brgy_name, COUNT(*) as visits 
        FROM passenger_trips 
        GROUP BY brgy_name 
        ORDER BY visits DESC 
        LIMIT 7";

$result = $conn->query($sql);

$labels = [];
$counts = [];

while ($row = $result->fetch_assoc()) {
    $labels[] = $row['brgy_name'];
    $counts[] = $row['visits'];
}

echo json_encode([
    'labels' => $labels,
    'counts' => $counts
]);
?>
