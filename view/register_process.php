<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'user_auth';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$full_name = $_POST['full_name'];
$username = $_POST['username'];
$dob = $_POST['dob'];
$gender = $_POST['gender'];
$nationality = $_POST['nationality'];
$address = $_POST['address'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$gov_id_type = $_POST['gov_id_type'];
$gov_id_number = $_POST['gov_id_number'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$sql = "INSERT INTO passenger (full_name, username, dob, gender, nationality, address, phone, email, gov_id_type, gov_id_number, password)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssss", $full_name, $username, $dob, $gender, $nationality, $address, $phone, $email, $gov_id_type, $gov_id_number, $password);

if ($stmt->execute()) {
    echo "Success";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
