<?php
// Include the Composer autoloader
include_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP(); // Use SMTP
    $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to Gmail
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = 'dreyujhon@gmail.com'; // Your Gmail address
    $mail->Password = 'erbg ilhs vhpz iiqj'; // Your Gmail password (Consider using OAuth or App Passwords for better security)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port = 587; // TCP port for TLS

    // Recipients
    $mail->setFrom('dreyujhon@gmail.com', 'Dreyu'); // Your email address and name
    $mail->addAddress('darrizdreyu@gmail.com', 'darrizdreyu'); // Recipient's email address and name

    // Content
    $mail->isHTML(true); // Set email format to HTML
    $mail->Subject = 'Test Email from PHPMailer';
    $mail->Body    = 'This is a test email sent from <b>PHPMailer</b> using Gmail SMTP!';
    $mail->AltBody = 'This is a test email sent from PHPMailer using Gmail SMTP in plain text.';

    // Send the email
    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    logError("Mailer Error: " . $mail->ErrorInfo, "ERROR");
    http_response_code(500);  // Internal Server Error
}
