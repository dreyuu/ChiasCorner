<?php
// Start the session if needed for some reason
// session_start(); // Not required if using JWT
include_once '../../connection.php';
require_once '../../vendor/autoload.php';  // Load the Composer autoloader
// Include JWT library
use \Firebase\JWT\JWT;

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
    $stmt = $connect->prepare("SELECT user_id, username, user_type, password FROM users WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Verify password using password_verify()
        if (password_verify($password, $user["password"])) {
            // Set token expiration time for the access token (2 days from now)
            $expires_at = time() + (2 * 24 * 60 * 60); // 2 days in seconds
            $refreshTokenExpiresAt = time() + (30 * 24 * 60 * 60); // 30 days for refresh token

            // Create the payload for the access token (JWT)
            $payload = [
                "user_id" => $user["user_id"],
                "username" => $user["username"],
                "user_type" => $user["user_type"],
                "iat" => time(),  // Issued At: current timestamp
                "exp" => $expires_at  // Expiry time for access token (2 days)
            ];

            // Create the payload for the refresh token
            $refreshPayload = [
                "user_id" => $user["user_id"],
                "username" => $user["username"],
                "user_type" => $user["user_type"],
                "iat" => time(),
                "exp" => $refreshTokenExpiresAt  // Expiry time for refresh token (30 days)
            ];

            // Define your secret key (use something complex and secure in production)
            $secretKey = "chiascornersercretkey";  // Change this to a more secure key

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
            exit();
        } else {
            echo json_encode(["success" => false, "message" => "Invalid username or password"]);
            exit();
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid username or password"]);
        exit();
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
