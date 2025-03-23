<?php
require '../../connection.php'; // Adjust the path based on your folder structure

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password
    $email = $_POST['email'];
    $user_type = $_POST['user_type'];

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
}
?>
