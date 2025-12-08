<?php
include_once __DIR__ . '/../../connection.php';
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if (!isset($_POST['username'])) {
    echo json_encode(['success' => false, 'message' => 'Username is required.']);
    exit;
}

$username = trim($_POST['username']);

try {
    // Step 1: Check if admin exists
    $stmt = $connect->prepare("SELECT * FROM users WHERE username = ? AND user_type = 'admin'");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo json_encode(['success' => false, 'message' => 'Invalid username.']);
        exit;
    }

    $admin_email = $admin['email'];

    // Step 2: Generate new credentials
    function generateUsername()
    {
        return 'admin' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
    }

    function generatePassword($length = 8)
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
    }

    $new_username = generateUsername();
    $new_password_plain = generatePassword();
    $new_password_hashed = password_hash($new_password_plain, PASSWORD_DEFAULT);

    // Step 3: Insert new admin account
    $default_name = "Recovered Admin";
    $insert = $connect->prepare("INSERT INTO users (name, username, password, email, user_type) VALUES (?, ?, ?, ?, 'admin')");
    $insert_success = $insert->execute([$default_name, $new_username, $new_password_hashed, $admin_email]);

    if ($insert_success) {
        // Step 4: Send credentials via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'dreyujhon@gmail.com'; // Your Gmail
            $mail->Password   = 'erbg ilhs vhpz iiqj';   // App password (not regular Gmail password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('darrizdreyu@gmail.com', "CHIA'S CORNER");
            $mail->addAddress('darrizdreu@gmail.com', 'Darriz Dreyu'); // Admin email

            $mail->isHTML(true);
            $mail->Subject = 'Temporary Admin Account';
            $mail->Body    = "Your new temporary admin account has been created:<br><br>
                                <strong>Username:</strong> $new_username<br>
                                <strong>Password:</strong> $new_password_plain<br><br>
                                Please change your credentials after logging in.";
            $mail->AltBody = "Username: $new_username\nPassword: $new_password_plain";

            $mail->send();
            echo json_encode(['success' => true, 'message' => 'New admin account created. Details sent to your email.']);
        } catch (Exception $e) {
            echo json_encode(['success' => true, 'message' => 'Account created, but email failed to send. Error: ' . $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create new admin account.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    logError("Database error: " . $e->getMessage(), "ERROR");
    http_response_code(500);  // Internal Server Error
}
