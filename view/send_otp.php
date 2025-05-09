<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

header('Content-Type: application/json'); // so we can return JSON for debugging

$response = [];

if (!isset($_SESSION['email_to']) || empty($_SESSION['email_to'])) {
    $response['status'] = 'fail';
    $response['error'] = 'Email not set in session';
    echo json_encode($response);
    exit;
}

$otp = mt_rand(100000, 999999);
$_SESSION['otp'] = $otp;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'gracecomeso0838@gmail.com'; // Your Gmail
    $mail->Password = 'xkkr mtve csfo hwgp';       // App password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('gracecomeso0838@gmail.com', 'Mambyahe');
    $mail->addAddress($_SESSION['email_to']);

    $mail->Subject = 'Your OTP Verification Code';
    $mail->Body    = 'Your OTP code is: ' . $otp;

    $mail->send();
    $response['status'] = 'success';
    $response['otp'] = $otp;
} catch (Exception $e) {
    $response['status'] = 'fail';
    $response['error'] = $mail->ErrorInfo;
}

echo json_encode($response);
