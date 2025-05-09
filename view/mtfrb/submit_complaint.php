<?php
session_start();
require_once '../../config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['passenger_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: complaint.php");
    exit();
}

try {
    // Validate required fields
    $required_fields = [
        'driver_name', 'plate_number', 'incident_date', 'incident_time',
        'pickup_location', 'destination', 'rejection_reason',
        'complaint_type', 'complaint_details'
    ];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Start transaction
    $pdo->beginTransaction();

    // Insert complaint into database
    $stmt = $pdo->prepare("
        INSERT INTO mtfrb_complaints (
            passenger_id,
            driver_name,
            plate_number,
            incident_date,
            incident_time,
            pickup_location,
            destination,
            rejection_reason,
            complaint_type,
            complaint_details,
            status,
            created_at
        ) VALUES (
            :passenger_id,
            :driver_name,
            :plate_number,
            :incident_date,
            :incident_time,
            :pickup_location,
            :destination,
            :rejection_reason,
            :complaint_type,
            :complaint_details,
            'pending',
            NOW()
        )
    ");

    $params = [
        'passenger_id' => $_SESSION['passenger_id'],
        'driver_name' => $_POST['driver_name'],
        'plate_number' => $_POST['plate_number'],
        'incident_date' => $_POST['incident_date'],
        'incident_time' => $_POST['incident_time'],
        'pickup_location' => $_POST['pickup_location'],
        'destination' => $_POST['destination'],
        'rejection_reason' => $_POST['rejection_reason'],
        'complaint_type' => $_POST['complaint_type'],
        'complaint_details' => $_POST['complaint_details']
    ];

    // Debug: Log the parameters
    error_log("Complaint submission parameters: " . print_r($params, true));

    $stmt->execute($params);
    $complaint_id = $pdo->lastInsertId();

    // Handle file uploads if any
    if (!empty($_FILES['supporting_docs']['name'][0])) {
        $upload_dir = '../../uploads/complaints/' . $complaint_id . '/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception("Failed to create upload directory: $upload_dir");
            }
        }

        foreach ($_FILES['supporting_docs']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['supporting_docs']['error'][$key] !== UPLOAD_ERR_OK) {
                throw new Exception("File upload error: " . $_FILES['supporting_docs']['error'][$key]);
            }

            $file_name = $_FILES['supporting_docs']['name'][$key];
            $file_type = $_FILES['supporting_docs']['type'][$key];
            $file_size = $_FILES['supporting_docs']['size'][$key];
            
            // Generate unique filename
            $unique_filename = uniqid() . '_' . $file_name;
            $file_path = $upload_dir . $unique_filename;
            
            // Move uploaded file
            if (!move_uploaded_file($tmp_name, $file_path)) {
                throw new Exception("Failed to move uploaded file: $file_name");
            }

            // Insert file record into database
            $stmt = $pdo->prepare("
                INSERT INTO complaint_documents (
                    complaint_id,
                    file_name,
                    file_path,
                    file_type,
                    file_size,
                    uploaded_at
                ) VALUES (
                    :complaint_id,
                    :file_name,
                    :file_path,
                    :file_type,
                    :file_size,
                    NOW()
                )
            ");
            
            $stmt->execute([
                'complaint_id' => $complaint_id,
                'file_name' => $file_name,
                'file_path' => $file_path,
                'file_type' => $file_type,
                'file_size' => $file_size
            ]);
        }
    }

    // Commit transaction
    $pdo->commit();

    // Set success message
    $_SESSION['success_message'] = "Your complaint has been submitted successfully. Reference ID: " . $complaint_id;
    header("Location: complaint.php");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log error
    error_log("Error submitting complaint: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Set error message
    $_SESSION['error_message'] = "An error occurred while submitting your complaint: " . $e->getMessage();
    header("Location: complaint.php");
    exit();
}
?> 