<?php
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = ['fullname', 'phone' , 'username', 'password', 'confirm_password', 'nationality', 'dob', 'address', 'franchise_no', 'or_no', 'make', 'motor_no', 'chassis_no', 'plate_no', 'toda', 'gov_id_type'];

    // Validate required fields
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo "Missing field: $field";
            exit;
        }
    }

    // Check password match
    if ($_POST['password'] !== $_POST['confirm_password']) {
        echo "Password mismatch";
        exit;
    }

    // --- CREDENTIAL CHECK ---
    // Check if credentials match a record in driver_credentials_check
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $username = $conn->real_escape_string($_POST['username']);
    $nationality = $conn->real_escape_string($_POST['nationality']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $address = $conn->real_escape_string($_POST['address']);
    $franchise_no = $conn->real_escape_string($_POST['franchise_no']);
    $or_no = $conn->real_escape_string($_POST['or_no']);
    $make = $conn->real_escape_string($_POST['make']);
    $motor_no = $conn->real_escape_string($_POST['motor_no']);
    $chassis_no = $conn->real_escape_string($_POST['chassis_no']);
    $plate_no = $conn->real_escape_string($_POST['plate_no']);
    $toda_id = intval($_POST['toda']);

    $check_sql = "SELECT * FROM driver_credentials_check WHERE fullname=? AND phone=? AND username=? AND nationality=? AND dob=? AND address=? AND franchise_no=? AND or_no=? AND make=? AND motor_no=? AND chassis_no=? AND plate_no=? AND toda_id=?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ssssssssssssi", $fullname, $phone, $username, $nationality, $dob, $address, $franchise_no, $or_no, $make, $motor_no, $chassis_no, $plate_no, $toda_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    if ($result->num_rows === 0) {
        echo "Error creating an account. Credentials do not match our records.";
        exit;
    }
    // --- END CREDENTIAL CHECK ---

    // Upload directory
    $uploadDir = 'uploads/driver_ids/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Handle government ID upload
    $govIdPath = '';
    if (isset($_FILES['gov_id_picture']) && $_FILES['gov_id_picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['gov_id_picture']['tmp_name'];
        $fileName = uniqid() . '_' . basename($_FILES['gov_id_picture']['name']);
        $govIdPath = 'driver_ids/' . $fileName;

        move_uploaded_file($fileTmpPath, $uploadDir . $fileName); // Store file to path
    } else {
        echo "ID upload error";
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $status = 'pending';

    // Prepare SQL insert
    $sql = "INSERT INTO driver (
                fullname, phone, username, password, nationality, dob, address,
                franchise_no, or_no, make, motor_no, chassis_no, plate_no,
                toda_id, gov_id_type, gov_id_picture, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind values
    $stmt->bind_param(
        "ssssssssssssissss",
        $_POST['fullname'],
        $_POST['phone'],
        $_POST['username'],
        $hashedPassword,
        $_POST['nationality'],
        $_POST['dob'],
        $_POST['address'],
        $_POST['franchise_no'],
        $_POST['or_no'],
        $_POST['make'],
        $_POST['motor_no'],
        $_POST['chassis_no'],
        $_POST['plate_no'],
        intval($_POST['toda']),
        $_POST['gov_id_type'],
        $govIdPath,
        $status
    );

    // Execute and respond
    echo $stmt->execute() ? "success" : "fail: " . $stmt->error;
} else {
    echo "Invalid request method";
}
?>
