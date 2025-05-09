<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if driver is logged in
if (!isset($_SESSION['driver_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Database credentials
$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Start transaction
    $pdo->beginTransaction();

    // Get current driver data
    $stmt = $pdo->prepare("SELECT * FROM driver WHERE driver_id = :driver_id");
    $stmt->execute(['driver_id' => $_SESSION['driver_id']]);
    $current_driver = $stmt->fetch(PDO::FETCH_ASSOC);

    // Handle profile picture upload
    $profile_pic = $current_driver['profile_pic']; // Keep existing picture by default
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/driver_ids/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        $new_filename = 'driver_' . $_SESSION['driver_id'] . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png'];
        if (!in_array($file_extension, $allowed_types)) {
            throw new Exception('Invalid file type. Only JPG, JPEG, and PNG files are allowed.');
        }

        // Validate file size (max 5MB)
        if ($_FILES['profile_pic']['size'] > 5 * 1024 * 1024) {
            throw new Exception('File size too large. Maximum size is 5MB.');
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
            // Delete old profile picture if exists
            if (!empty($current_driver['profile_pic']) && file_exists($upload_dir . $current_driver['profile_pic'])) {
                unlink($upload_dir . $current_driver['profile_pic']);
            }
            $profile_pic = $new_filename;
        } else {
            throw new Exception('Failed to upload profile picture.');
        }
    }

    // Update driver information
    $stmt = $pdo->prepare("
        UPDATE driver 
        SET fullname = :fullname,
            username = :username,
            phone = :phone,
            nationality = :nationality,
            dob = :dob,
            address = :address,
            toda = :toda,
            franchise_no = :franchise_no,
            or_no = :or_no,
            make = :make,
            motor_no = :motor_no,
            chassis_no = :chassis_no,
            plate_no = :plate_no,
            profile_pic = :profile_pic
        WHERE driver_id = :driver_id
    ");

    $stmt->execute([
        'fullname' => $_POST['fullname'],
        'username' => $_POST['username'],
        'phone' => $_POST['phone'],
        'nationality' => $_POST['nationality'],
        'dob' => $_POST['dob'],
        'address' => $_POST['address'],
        'toda' => $_POST['toda'],
        'franchise_no' => $_POST['franchise_no'],
        'or_no' => $_POST['or_no'],
        'make' => $_POST['make'],
        'motor_no' => $_POST['motor_no'],
        'chassis_no' => $_POST['chassis_no'],
        'plate_no' => $_POST['plate_no'],
        'profile_pic' => $profile_pic,
        'driver_id' => $_SESSION['driver_id']
    ]);

    // Commit transaction
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error updating driver profile: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 