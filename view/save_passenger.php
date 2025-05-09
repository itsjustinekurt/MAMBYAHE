<?php
session_start();
header("Content-Type: application/json");

// Decode JSON input
$data = json_decode(file_get_contents("php://input"), true);

// List of required fields
$required_fields = [
    'fullname', 'username', 'dob', 'gender', 'nationality',
    'address', 'phone', 'email', 'gov_id', 'id_type', 'password'
];

// Check for missing fields
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        echo json_encode([
            "status" => "error",
            "error" => "Missing field: " . $field
        ]);
        exit();
    }
}

// Connect to database
$conn = new mysqli("localhost", "root", "", "user_auth");
if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "error" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}

// Escape input values and hash password
$fullname = $conn->real_escape_string($data['fullname']);
$username = $conn->real_escape_string($data['username']);
$dob = $conn->real_escape_string($data['dob']);
$gender = $conn->real_escape_string($data['gender']);
$nationality = $conn->real_escape_string($data['nationality']);
$address = $conn->real_escape_string($data['address']);
$phone = $conn->real_escape_string($data['phone']);
$email = $conn->real_escape_string($data['email']);
$gov_id = $conn->real_escape_string($data['gov_id']);
$id_type = $conn->real_escape_string($data['id_type']);
$password = password_hash($data['password'], PASSWORD_DEFAULT);

// Optional: Check for duplicate username or email
$check = $conn->prepare("SELECT passenger_id FROM passenger WHERE username = ? OR email = ?");
$check->bind_param("ss", $username, $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode([
        "status" => "error",
        "error" => "Username or Email already exists"
    ]);
    exit();
}
$check->close();

// Insert into database
$stmt = $conn->prepare("INSERT INTO passenger (fullname, username, dob, gender, nationality, address, phone, email, gov_id, id_type, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssssss", $fullname, $username, $dob, $gender, $nationality, $address, $phone, $email, $gov_id, $id_type, $password);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode([
        "status" => "error",
        "error" => "Database error: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
