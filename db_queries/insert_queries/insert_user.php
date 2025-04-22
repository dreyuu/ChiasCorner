<?php
require '../../connection.php';
require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

$secretKey = "chiascornersercretkey";

// Get token from header
$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized - No token provided"]);
    exit;
}

$token = str_replace("Bearer ", "", $headers['Authorization']);

try {
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

    // Optional: check if user has admin rights
    if ($decoded->user_type !== "admin") {
        http_response_code(403);
        echo json_encode(["error" => "Forbidden - Admins only"]);
        exit;
    }

    // Get the raw POST data
    $inputData = json_decode(file_get_contents("php://input"), true);

    $name = $inputData['name'] ?? null;
    $username = $inputData['username'] ?? null;
    $password = isset($inputData['password']) ? password_hash($inputData['password'], PASSWORD_DEFAULT) : null;
    $email = $inputData['email'] ?? null;
    $user_type = $inputData['user_type'] ?? null;

    // Validate required fields
    if (!$name || !$username || !$password || !$email || !$user_type) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit;
    }

    // Insert into DB
    $query = "INSERT INTO users (name, username, password, email, user_type) 
                VALUES (:name, :username, :password, :email, :user_type)";
    $stmt = $connect->prepare($query);
    $stmt->execute([
        ':name' => $name,
        ':username' => $username,
        ':password' => $password,
        ':email' => $email,
        ':user_type' => $user_type
    ]);

    echo json_encode(["success" => true, "message" => "User added successfully!"]);

} catch (ExpiredException $e) {
    http_response_code(401);
    echo json_encode(["error" => "Token has expired"]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid token"]);
}
