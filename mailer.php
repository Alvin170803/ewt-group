<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_email($token){
    require 'vendor/autoload.php';

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = ''; 
    $mail->Password = ''; 
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('your-email@gmail.com', 'User Registration');
    $mail->addAddress("sekyezaelroy@gmail.com");
    $mail->Subject = "Password Reset Code";
    $mail->Body = "Code: $token";

    $mail->send();
    return true ;
} catch (Exception $e) {
    return false;
}
}
