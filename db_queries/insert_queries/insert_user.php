<?php
include_once __DIR__ . '/../../connection.php';
include_once __DIR__ . '/../../vendor/autoload.php';
include_once __DIR__ . '/../../components/system_log.php';
include_once __DIR__ . '/../../components/pusher_helper.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Dotenv\Dotenv;

// Load env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();
$secretKey = $_ENV['JWS_SECRET_KEY'];

// Get token from header
// $headers = apache_request_headers();
// if (!isset($headers['Authorization'])) {
//     http_response_code(401);
//     echo json_encode(["error" => "Unauthorized - No token provided"]);
//     exit;
// }

// $token = str_replace("Bearer ", "", $headers['Authorization']);

try {
    // $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

    // Optional: check if user has admin rights
    // if ($decoded->user_type !== "admin") {
    //     http_response_code(403);
    //     echo json_encode(["error" => "Forbidden - Admins only"]);
    //     exit;
    // }

    // Get the raw POST data
    $inputData = json_decode(file_get_contents("php://input"), true);

    $ownerID = $inputData['owner_id'] ?? null;
    $name = $inputData['name'] ?? null;
    $username = $inputData['username'] ?? null;
    $passwordInput = $inputData['password'] ?? null;
    $email = $inputData['email'] ?? null;
    $user_type = $inputData['user_type'] ?? null;
    $status = $inputData['status'] ?? null;

    // Validate required fields
    if (!$name || !$username || !$passwordInput || !$email || !$user_type || !$status) {
        // http_response_code(400);
        echo json_encode(["success" => false, "message" => "Missing required fields", "status" => "warning"]);
        exit;
    }
    // Hash the password
    $password = password_hash($passwordInput, PASSWORD_DEFAULT);
    // Generate random PIN
    $pin = random_int(100000, 999999);
    if ($user_type === 'admin' || $user_type === 'dev') {
        // Insert into DB
        $query = "INSERT INTO users (name, username, password, email, user_type, status, auth_pin, date_created)
                VALUES (:name, :username, :password, :email, :user_type, :status, :auth_pin, :date_created)";
        $stmt = $connect->prepare($query);
        $stmt->execute([
            ':name' => $name,
            ':username' => $username,
            ':password' => $password,
            ':email' => $email,
            ':user_type' => $user_type,
            ':status' => $status,
            ':auth_pin' => $pin,
            ':date_created' => localNow()
        ]);
    } else {
        // Insert into DB
        $query = "INSERT INTO users (name, username, password, email, user_type, status, date_created)
                VALUES (:name, :username, :password, :email, :user_type, :status, :date_created)";
        $stmt = $connect->prepare($query);
        $stmt->execute([
            ':name' => $name,
            ':username' => $username,
            ':password' => $password,
            ':email' => $email,
            ':user_type' => $user_type,
            ':status' => $status,
            ':date_created' => localNow()
        ]);
    }


    echo json_encode(["success" => true, "message" => "User added successfully!", "status" => "success"]);
    PusherHelper::send("users-channel", "modify-user", ["msg" => "User added successfully"]);
    logAction(
        $connect,
        $ownerID,        // admin who created the user
        'USER',          // NOT AUTH
        'USER_CREATE',   // specific action type
        "Created user: $username"
    );
} catch (ExpiredException $e) {
    http_response_code(401);
    echo json_encode(["error" => "Token has expired"]);
    logError("Token has expired: " . $e->getMessage(), "ERROR");
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid token"]);
    logError("Invalid token: " . $e->getMessage(), "ERROR");
}
