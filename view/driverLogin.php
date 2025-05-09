<?php
session_start();
include '../connection.php'; // Adjust path as needed

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Look up driver by username
    $sql = "SELECT * FROM driver WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            if ($row['status'] === 'approved') {
                // Login successful
                $_SESSION['driver_id'] = $row['id'];
                $_SESSION['driver_name'] = $row['fullname'];
                
                // Update driver's online status
                $updateStmt = $conn->prepare("UPDATE driver SET is_online = 'online' WHERE driver_id = ?");
                $updateStmt->bind_param("i", $row['id']);
                $updateStmt->execute();
                
                header("Location: driver_dashboard.php");
                exit();
            } elseif ($row['status'] === 'pending') {
                echo "<script>
                    alert('Your account is still pending MTFRB approval.');
                    window.location.href='driverslogin.php';
                </script>";
            } else {
                echo "<script>
                    alert('Your account has been rejected by MTFRB.');
                    window.location.href='driverslogin.php';
                </script>";
            }
        } else {
            echo "<script>
                alert('Incorrect password.');
                window.location.href='driverslogin.php';
            </script>";
        }
    } else {
        echo "<script>
            alert('Username not found.');
            window.location.href='driverslogin.php';
        </script>";
    }
}
?>
