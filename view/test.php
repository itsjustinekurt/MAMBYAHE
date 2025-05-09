<?php
session_start();
$_SESSION['email_to'] = 'comesomariejoy19@gmail.com'; // Replace with YOUR test email
header('Location: send_otp.php');
exit;
