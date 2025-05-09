<?php
session_start();
echo json_encode([
  "loggedIn" => isset($_SESSION['passenger_id']) && isset($_SESSION['fullname'])
]);
?>
