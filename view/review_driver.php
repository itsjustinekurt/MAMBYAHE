<?php
// review_driver.php
session_start();
include '../view/connection.php'; // Ensure this path is correct

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driver_id = $_POST['driver_id'];

    if (isset($_POST['approve'])) {
        $update = "UPDATE driver SET status = 'approved' WHERE driver_id = $driver_id";
        mysqli_query($conn, $update);

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
          Swal.fire({
            icon: 'success',
            title: 'Account Approved!',
            confirmButtonText: 'OK'
          }).then(() => {
            window.location.href = 'all_users.php';
          });
        </script>";
        exit;
    } elseif (isset($_POST['reject'])) {
        $update = "UPDATE driver SET status = 'rejected' WHERE driver_id = $driver_id";
        mysqli_query($conn, $update);

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
          Swal.fire({
            icon: 'info',
            title: 'Account Rejected!',
            confirmButtonText: 'OK'
          }).then(() => {
            window.location.href = 'mtfrb_dashboard.php';
          });
        </script>";
        exit;
    }
}
?>
