<?php
require_once 'csrf_handler.php';

// Database credentials
$db_host = 'localhost';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get CSRF token from request
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Also check headers
    if (empty($csrf_token)) {
        $csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    }
    
    // Verify CSRF token
    if (!validate_csrf_token($csrf_token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }

    try {
        // Get current password from request
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $username = $_POST['username'] ?? '';

        // Validate required fields
        if (empty($username)) {
            throw new Exception('Username is required');
        }

        // Validate password requirements
        if (!empty($new_password)) {
            if (strlen($new_password) < 8) {
                throw new Exception('Password must be at least 8 characters long');
            }
            if ($new_password !== $confirm_password) {
                throw new Exception('New passwords do not match');
            }
        }

        // Connect to database
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get current user's password
        $stmt = $pdo->prepare("SELECT password FROM driver WHERE driver_id = :driver_id");
        $stmt->execute(['driver_id' => $_SESSION['driver_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify current password if changing password
        if (!empty($new_password)) {
            if (empty($current_password)) {
                throw new Exception('Current password is required when changing password');
            }
            if (!password_verify($current_password, $user['password'])) {
                throw new Exception('Current password is incorrect');
            }
        }

        // Prepare update query
        $stmt = $pdo->prepare("UPDATE driver SET username = :username WHERE driver_id = :driver_id");
        
        // Update password if provided
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE driver SET username = :username, password = :password WHERE driver_id = :driver_id");
            $stmt->execute([
                'username' => $username,
                'password' => $hashed_password,
                'driver_id' => $_SESSION['driver_id']
            ]);
        } else {
            $stmt->execute([
                'username' => $username,
                'driver_id' => $_SESSION['driver_id']
            ]);
        }

        // Get updated user data
        $stmt = $pdo->prepare("SELECT username FROM driver WHERE driver_id = :driver_id");
        $stmt->execute(['driver_id' => $_SESSION['driver_id']]);
        $updated_user = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Account settings updated successfully',
            'username' => $updated_user['username']
        ]);

    } catch (Exception $e) {
        error_log("Account update error: " . $e->getMessage());
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
