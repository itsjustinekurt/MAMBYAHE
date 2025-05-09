<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$result = $conn->query("SELECT id, name FROM associations ORDER BY name ASC");
$associations = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $associations[] = $row;
    }
}
echo json_encode($associations);
?> 