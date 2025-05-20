<?php
session_start();
require_once 'csrf_handler.php';

// Database credentials
$db_host = 'localhost';
$db_db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['driver_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

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
        // Get settings from request
        $language = $_POST['language'] ?? 'en';
        $theme = $_POST['theme'] ?? 'light';
        $distance_unit = $_POST['distance_unit'] ?? 'km';
        $driver_id = $_POST['driver_id'] ?? null;

        // Validate settings
        $valid_languages = ['en', 'tl'];
        $valid_themes = ['light', 'dark', 'system'];
        $valid_distance_units = ['km', 'mi'];

        // Validate required fields
        if (empty($driver_id) || $driver_id != $_SESSION['driver_id']) {
            throw new Exception('Invalid driver ID');
        }

        if (!in_array($language, $valid_languages)) {
            throw new Exception('Invalid language selected');
        }

        if (!in_array($theme, $valid_themes)) {
            throw new Exception('Invalid theme selected');
        }

        if (!in_array($distance_unit, $valid_distance_units)) {
            throw new Exception('Invalid distance unit selected');
        }

        // Connect to database
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_db_name", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }

        // Update app settings in session first
        $_SESSION['language'] = $language;
        $_SESSION['theme'] = $theme;
        $_SESSION['distance_unit'] = $distance_unit;

        // Update in database
        try {
            $stmt = $pdo->prepare("UPDATE driver SET 
                language = :language,
                theme = :theme,
                distance_unit = :distance_unit
                WHERE driver_id = :driver_id");

            $stmt->execute([
                'language' => $language,
                'theme' => $theme,
                'distance_unit' => $distance_unit,
                'driver_id' => $driver_id
            ]);

            // Get updated settings
            $stmt = $pdo->prepare("SELECT language, theme, distance_unit FROM driver WHERE driver_id = :driver_id");
            $stmt->execute(['driver_id' => $driver_id]);
            $updated_settings = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'message' => 'App settings updated successfully',
                'settings' => $updated_settings
            ]);
        } catch (PDOException $e) {
            throw new Exception('Database error: ' . $e->getMessage());
        }

    } catch (Exception $e) {
        // Log the error
        error_log("App settings update error: " . $e->getMessage());
        
        // Send proper JSON response
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
} else {
    // Send proper JSON response for invalid method
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}
