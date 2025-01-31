<?php
session_start();
require 'vendor/autoload.php';
require_once 'db_connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    
    // Generate 6-digit OTP
    $otp = sprintf("%06d", mt_rand(1, 999999));
    
    // Store OTP in session for verification
    $_SESSION['otp'] = [
        'code' => $otp,
        'user_id' => $user_id,
        'expires' => time() + (5 * 60) // 5 minutes expiration
    ];
    
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output
        $mail->isSMTP();                       // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';  // Set the SMTP server to send through
        $mail->SMTPAuth   = true;              // Enable SMTP authentication
        $mail->Username   = 'untalancarlo1216@gmail.com';  // SMTP username
        $mail->Password   = 'pqtf pbmz cgdj qyee';     // SMTP password (App Password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Enable TLS encryption
        $mail->Port       = 587;               // TCP port to connect to

        // Disable SSL certificate verification (not recommended for production)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

        // Recipients
        $mail->setFrom('your-email@gmail.com', 'Darth Vader');
        $mail->addAddress('untalancarlo1216@gmail.com');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Delete Confirmation OTP';
        $mail->Body    = "
            <h2>Delete Confirmation OTP</h2>
            <p>Your OTP for deletion confirmation is: <strong>{$otp}</strong></p>
            <p>This OTP will expire in 5 minutes.</p>
            <p>If you did not request this deletion, please ignore this email.</p>
        ";

        $mail->send();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}