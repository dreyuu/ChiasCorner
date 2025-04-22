<?php
include_once '../../connection.php';
require '../../vendor/autoload.php'; // For JWT
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "chiascornersercretkey";  // Same key used for signing the tokens

// Check if the refresh token is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['refresh_token'])) {
    $refreshToken = $_POST['refresh_token'];
    
    try {
        // Decode the refresh token
        $decodedRefresh = JWT::decode($refreshToken, new Key($secretKey, 'HS256'));

        // Check if the refresh token is still valid
        if ($decodedRefresh->exp < time()) {
            throw new Exception('Refresh token expired');
        }

        // Generate a new access token (JWT)
        $newJwt = JWT::encode([
            "user_id" => $decodedRefresh->user_id,
            "username" => $decodedRefresh->username,
            "user_type" => $decodedRefresh->user_type,
            "iat" => time(),
            "exp" => time() + (2 * 24 * 60 * 60)  // New JWT expires in 2 days
        ], $secretKey, 'HS256');
        
        // Respond with the new access token
        echo json_encode([
            "success" => true,
            "token" => $newJwt
        ]);

    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid or expired refresh token"
        ]);
    }
}
