<?php
include_once '../../connection.php';
require '../../vendor/autoload.php'; // For JWT
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

$secretKey = "chiascornersercretkey";

$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized - Token missing"]);
    exit;
}

$token = str_replace("Bearer ", "", $headers['Authorization']);

try {
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

    // Optional: check if user has admin rights
    if ($decoded->user_type !== "admin") {
        http_response_code(403);
        echo json_encode(["error" => "Forbidden - Admin only"]);
        exit;
    }

    // Get the raw POST data
    $inputData = json_decode(file_get_contents("php://input"), true);

    $userId = $inputData['user_id'] ?? null;
    $name = $inputData['name'] ?? null;
    $username = $inputData['username'] ?? null;
    $password = $inputData['password'] ?? null;
    $email = $inputData['email'] ?? null;
    $user_type = $inputData['user_type'] ?? null;

    if (!$userId || !$name || !$username || !$email || !$user_type) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit;
    }

    try {
        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET name = :name, username = :username, password = :password, email = :email, user_type = :user_type WHERE user_id = :user_id";
        } else {
            $query = "UPDATE users SET name = :name, username = :username, email = :email, user_type = :user_type WHERE user_id = :user_id";
            $hashedPassword = null;
        }

        $stmt = $connect->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        if ($hashedPassword) {
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        }
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':user_type', $user_type, PDO::PARAM_STR);

        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating user: ' . $e->getMessage()]);
    }

} catch (ExpiredException $e) {
    http_response_code(401);
    echo json_encode(["error" => "Token expired"]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid token"]);
}
