<?php
include_once __DIR__ . '/../../connection.php';
include_once __DIR__ . '/../../components/pusher_helper.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader

try {
    // Check for POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        logError("Invalid request method for remove_user.php", "ERROR");
        http_response_code(405);  // Method Not Allowed
        exit;
    }

    // Get user_id from POST data
    $userId = $_POST['user_id'] ?? null;

    // Validate user_id
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        logError("Remove user error: User ID is required", "ERROR");
        http_response_code(400);  // Bad Request
        exit;
    }

    // Prepare SQL to delete the user
    $stmt = $connect->prepare("DELETE FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Account removed successfully', "status" => "success"]);

        // Notify other users via Pusher
        PusherHelper::send("users-channel", "modify-user", ["msg" => "User removed successfully"]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove account']);
        logError("Remove user error: Failed to execute delete query", "ERROR");
        http_response_code(500);  // Internal Server Error
    }
} catch (PDOException $e) {
    // Log the error and respond with a message
    logError("Database error: " . $e->getMessage(), "ERROR");
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    http_response_code(500);  // Internal Server Error
} catch (Exception $e) {
    // Catch any other exceptions
    logError("General error: " . $e->getMessage(), "ERROR");
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred']);
    http_response_code(500);  // Internal Server Error
}
