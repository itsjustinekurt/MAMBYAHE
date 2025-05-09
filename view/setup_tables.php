<?php
// Database credentials
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'user_auth';

try {
    // First connect without database name to create it if it doesn't exist
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
    echo "Database '$db_name' checked/created successfully.<br>";

    // Now connect to the specific database
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read SQL file
    $sql = file_get_contents('create_tables.sql');

    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    // Execute each statement separately
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                echo "Executed: " . substr($statement, 0, 50) . "...<br>";
            } catch (PDOException $e) {
                echo "Error executing statement: " . substr($statement, 0, 50) . "...<br>";
                echo "Error message: " . $e->getMessage() . "<br><br>";
            }
        }
    }

    echo "<br>Setup completed!";

} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
} 