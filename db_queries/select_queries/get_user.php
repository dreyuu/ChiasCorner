<?php
// Start the session if needed for some reason
// session_start(); // Not required if using JWT
include_once __DIR__ . '/../../connection.php';
require __DIR__ . '/../../vendor/autoload.php';  // Load the Composer autoloader
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader
require __DIR__ . '/../../components/system_log.php';
// Include JWT library
use \Firebase\JWT\JWT;

use Dotenv\Dotenv;
// Load env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();
// Include database connection

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

$username = trim($_POST["username"] ?? '');
$password = trim($_POST["password"] ?? '');

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Username and password are required"]);
    exit();
}

try {
    // Prepare query to fetch user details
    $stmt = $connect->prepare("SELECT user_id, name, username, user_type, password, status FROM users WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["success" => false, "message" => "Invalid username or password"]);
        logAction(
            $connect,
            null,
            'AUTH',
            'LOGIN_FAILED',
            "Failed login attempt for username: $username"
        );
        exit();
    }

    if (!password_verify($password, $user["password"])) {
        echo json_encode(["success" => false, "message" => "Invalid username or password"]);
        logAction(
            $connect,
            null,
            'AUTH',
            'LOGIN_FAILED',
            "Failed login attempt for username: $username"
        );
        exit();
    }

    if ($user["status"] !== 'active') {
        echo json_encode(["success" => false, "message" => "User account is not active"]);
        logAction(
            $connect,
            null,
            'AUTH',
            'LOGIN_FAILED',
            "Failed login attempt for username: $username - Inactive account"
        );

        exit();
    }

    // Set token expiration time for the access token (2 days from now)
    $expires_at = time() + (2 * 24 * 60 * 60); // 2 days in seconds
    $refreshTokenExpiresAt = time() + (30 * 24 * 60 * 60); // 30 days for refresh token

    // Create the payload for the access token (JWT)
    $payload = [
        "user_id" => $user["user_id"],
        "name" => $user["name"],
        "username" => $user["username"],
        "user_type" => $user["user_type"],
        "iat" => time(),  // Issued At: current timestamp
        "exp" => $expires_at  // Expiry time for access token (2 days)
    ];

    // Create the payload for the refresh token
    $refreshPayload = [
        "user_id" => $user["user_id"],
        "name" => $user["name"],
        "username" => $user["username"],
        "user_type" => $user["user_type"],
        "iat" => time(),
        "exp" => $refreshTokenExpiresAt  // Expiry time for refresh token (30 days)
    ];

    // Define your secret key (use something complex and secure in production)
    $secretKey = $_ENV['JWS_SECRET_KEY'];  // Change this to a more secure key

    // Encode the access token using the payload, secret key, and algorithm (HS256)
    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    // Encode the refresh token using the refresh payload
    $refreshToken = JWT::encode($refreshPayload, $secretKey, 'HS256');

    // Respond with both the JWT access token and the refresh token
    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "token" => $jwt,
        "refresh_token" => $refreshToken  // Include the refresh token in the response
    ]);

    logAction(
        $connect,
        $user['user_id'],
        'AUTH',
        'LOGIN',
        'User logged in'
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    logError("Database error: " . $e->getMessage(), "ERROR");
}
