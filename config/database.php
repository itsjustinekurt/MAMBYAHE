<?php
// Database credentials
$db_host = '127.0.0.1';
$db_name = 'user_auth';
$db_user = 'root';
$db_pass = '';

try {
    // Establish PDO connection with timeout
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5, // 5 second timeout
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        )
    );
} catch (PDOException $e) {
    // More detailed error message
    die("Database connection failed: " . $e->getMessage() . 
        "\nPlease make sure MySQL is running in XAMPP Control Panel.");
}
?> 