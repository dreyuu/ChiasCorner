<?php
require __DIR__ . '/../../vendor/autoload.php'; // For JWT
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "chiascornersercretkey";

$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$token = str_replace("Bearer ", "", $headers['Authorization']);

$decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

// Optional: check if user has admin rights
if ($decoded->usertype !== "admin") {
    http_response_code(403);
    echo json_encode(["error" => "Forbidden"]);
    logError("Unauthorized access attempt by user: " . $decoded->username, "ERROR");
    exit;
}
