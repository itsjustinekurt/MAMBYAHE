<?php
require_once '../db_connect.php';

header('Content-Type: application/json');

try {
    // Get all form data
    $data = [
        'fullname' => $_POST['fullname'],
        'phone' => $_POST['phone'],
        'username' => $_POST['username'],
        'nationality' => $_POST['nationality'],
        'dob' => $_POST['dob'],
        'address' => $_POST['address'],
        'franchise_no' => $_POST['franchise_no'],
        'or_no' => $_POST['or_no'],
        'make' => $_POST['make'],
        'motor_no' => $_POST['motor_no'],
        'chassis_no' => $_POST['chassis_no'],
        'plate_no' => $_POST['plate_no'],
        'toda_id' => $_POST['toda_name']
    ];

    // Prepare and execute the insert query
    $stmt = $conn->prepare("INSERT INTO driver_credentials_check (
        fullname, phone, username, nationality, dob, address, 
        franchise_no, or_no, make, motor_no, chassis_no, plate_no, toda_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssssssssssss", 
        $data['fullname'], $data['phone'], $data['username'], $data['nationality'],
        $data['dob'], $data['address'], $data['franchise_no'], $data['or_no'],
        $data['make'], $data['motor_no'], $data['chassis_no'], $data['plate_no'],
        $data['toda_id']
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Driver added successfully']);
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
