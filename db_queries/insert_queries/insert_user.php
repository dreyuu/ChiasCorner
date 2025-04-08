<?php
require '../../connection.php'; // Adjust the path based on your folder structure


// Get the raw POST data
$inputData = json_decode(file_get_contents("php://input"), true);

$name = $inputData['name'] ?? null;
$username = $inputData['username'] ?? null;
$password = password_hash($inputData['password'], PASSWORD_DEFAULT) ?? null; // Secure password
$email = $inputData['email'] ?? null;
$user_type = $inputData['user_type'] ?? null;

try {
    // Prepare the SQL statement
    $query = "INSERT INTO users (name, username, password, email, user_type) 
                    VALUES (:name, :username, :password, :email, :user_type)";
    $stmt = $connect->prepare($query);

    // Execute with user inputs
    $stmt->execute([
        ':name' => $name,
        ':username' => $username,
        ':password' => $password,
        ':email' => $email,
        ':user_type' => $user_type
    ]);

    echo json_encode(["success" => true, "message" => "User added successfully!"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
