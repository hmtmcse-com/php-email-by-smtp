<?php
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// reCAPTCHA v3 verification
$recaptchaSecret = 'YOUR_SECRET_KEY';
$token = isset($_POST['recaptcha_token']) ? $_POST['recaptcha_token'] : '';

$recaptchaResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$token");
$responseData = json_decode($recaptchaResponse);

if (!$responseData->success || $responseData->score < 0.5) {
    echo "❌ reCAPTCHA failed. Score: " . (isset($responseData->score) ? $responseData->score : 'unknown');
    exit;
}

// Basic validation
$name    = strip_tags(isset($_POST['name']) ? $_POST['name'] : '');
$email   = filter_var(isset($_POST['email']) ? $_POST['email'] : '', FILTER_VALIDATE_EMAIL);
$phone   = strip_tags(isset($_POST['phone']) ? $_POST['phone'] : '');
$comment = strip_tags(isset($_POST['comment']) ? $_POST['comment'] : '');

if (!$name || !$email || !$comment) {
    echo "❌ Please fill in all required fields.";
    exit;
}

// Send mail using PHPMailer
$mail = new PHPMailer(true);
try {
    // SMTP setup
    $mail->isSMTP();
    $mail->Host       = 'smtp.yourdomain.com'; // Your SMTP host
    $mail->SMTPAuth   = true;
    $mail->Username   = 'your_email@yourdomain.com';
    $mail->Password   = 'your_password';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Optional: disable SSL verification
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true
        )
    );

    // Recipients
    $mail->setFrom('your_email@yourdomain.com', 'Website Contact');
    $mail->addAddress('recipient@example.com');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Contact Form Submission';
    $mail->Body    = "
        <strong>Name:</strong> $name <br>
        <strong>Email:</strong> $email <br>
        <strong>Phone:</strong> $phone <br>
        <strong>Comment:</strong><br>$comment
    ";
    $mail->AltBody = "Name: $name\nEmail: $email\nPhone: $phone\nComment:\n$comment";

    $mail->send();
    echo "✅ Message sent successfully!";
} catch (Exception $e) {
    echo "❌ Error: {$mail->ErrorInfo}";
}
