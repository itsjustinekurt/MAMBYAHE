<?php
session_start();

// Verify OTP
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
    $userOtp = trim($_POST['otp']);

    // Compare entered OTP with the session OTP
    if ($userOtp == $_SESSION['otp']) {
        echo "success"; // OTP matches
        unset($_SESSION['otp']); // Clear OTP after successful verification
    } else {
        echo "fail"; // OTP mismatch
    }
}
?>
