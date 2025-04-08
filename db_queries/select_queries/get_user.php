<?php
session_start();
include_once '../../connection.php';

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
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["user_type"] = $user["user_type"];
            $_SESSION["logged_in"] = true;

            echo json_encode(["success" => true, "message" => "Login successful"]);
            exit();
        } else {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Invalid username or password"]);
            exit();
        }
    } else {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Invalid username or password"]);
        exit();
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
