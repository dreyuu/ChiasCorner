<?php
include_once '../../connection.php';

// Get the raw POST data
$inputData = json_decode(file_get_contents("php://input"), true);

// Debugging: Check the input data
file_put_contents('php://stderr', print_r($inputData, true));

$userId = $inputData['user_id'] ?? null;
$name = $inputData['name'] ?? null;
$username = $inputData['username'] ?? null;
$password = $inputData['password'] ?? null;
$email = $inputData['email'] ?? null;
$user_type = $inputData['user_type'] ?? null;

// Debugging: Check the variables
file_put_contents('php://stderr', "userId: " . $userId . "\n");
file_put_contents('php://stderr', "name: " . $name . "\n");

try {
    // Check if password is provided (only hash it if it's not empty)
    if ($password) {
        // Hash the password if it's provided
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET name = :name, username = :username, password = :password, email = :email, user_type = :user_type WHERE user_id = :user_id";
    } else {
        // If no password is provided, exclude it from the update query
        $query = "UPDATE users SET name = :name, username = :username, email = :email, user_type = :user_type WHERE user_id = :user_id";
        $hashedPassword = null;  // No password change
    }

    $stmt = $connect->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    if ($hashedPassword) {
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);  // Bind hashed password if it exists
    }
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':user_type', $user_type, PDO::PARAM_STR);

    $stmt->execute();
    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error updating user: ' . $e->getMessage()]);
}
?>
