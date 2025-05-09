<?php
require_once "connection.php"; // Your DB connection file

header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true);

$response = ["status" => "available"];

if (isset($data['type']) && $data['type'] === "driver") {
  $email = $data['email'];
  $username = $data['username'];
  $plate_no = $data['plate_no'];
  $gov_id_number = $data['gov_id_number'];

  $stmt = $conn->prepare("SELECT * FROM driver WHERE email = ? OR username = ? OR plate_no = ? OR gov_id_number = ?");
  $stmt->bind_param("ssss", $email, $username, $plate_no, $gov_id_number);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      if ($row['email'] === $email) $response['email'] = "unavailable";
      if ($row['username'] === $username) $response['username'] = "unavailable";
      if ($row['plate_no'] === $plate_no) $response['plate_no'] = "unavailable";
      if ($row['gov_id_number'] === $gov_id_number) $response['gov_id_number'] = "unavailable";
    }
    $response['status'] = "duplicate";
  }
} elseif (isset($data['type']) && $data['type'] === "passenger") {
  $email = $data['email'];
  $username = $data['username'];
  $gov_id_number = $data['gov_id_number'];
  $id_type = $data['id_type'];

  $stmt = $conn->prepare("SELECT * FROM passenger WHERE email = ? OR username = ? OR (gov_id_number = ? AND id_type = ?)");
  $stmt->bind_param("ssss", $email, $username, $gov_id_number, $id_type);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      if ($row['email'] === $email) $response['email'] = "unavailable";
      if ($row['username'] === $username) $response['username'] = "unavailable";
      if ($row['gov_id_number'] === $gov_id_number && $row['id_type'] === $id_type) {
        $response['gov_id_number'] = "unavailable";
      }
    }
    $response['status'] = "duplicate";
  }
}

echo json_encode($response);
