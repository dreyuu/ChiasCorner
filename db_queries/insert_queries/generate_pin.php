<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../connection.php";
require __DIR__ . '/../../components/logger.php';

// Read JSON
$input = json_decode(file_get_contents("php://input"), true);

try {

    if (!isset($input['user_id'])) {
        echo json_encode([
            "success" => false,
            "message" => "User ID not provided."
        ]);
        logError("User ID not provided in generate_pin.php", "WARNING");
        exit;
    }

    $user_id = intval($input['user_id']);

    // Check if user is admin (PDO)
    $check = $connect->prepare("SELECT user_type FROM users WHERE user_id = :id");
    $check->bindValue(':id', $user_id, PDO::PARAM_INT);
    $check->execute();
    $row = $check->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode([
            "success" => false,
            "message" => "User not found."
        ]);
        exit;
    }

    if ($row['user_type'] !== "admin") {
        echo json_encode([
            "success" => false,
            "message" => "Only admins can generate a PIN."
        ]);
        exit;
    }

    // Generate random PIN
    $pin = random_int(100000, 999999);

    // Update PIN (PDO)
    $update = $connect->prepare("UPDATE users SET auth_pin = :pin WHERE user_id = :id");
    $update->bindValue(':pin', $pin, PDO::PARAM_STR);
    $update->bindValue(':id', $user_id, PDO::PARAM_INT);

    if ($update->execute()) {
        echo json_encode([
            "success" => true,
            "pin" => $pin
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to save PIN."
        ]);
    }
} catch (Throwable $th) {
    echo json_encode([
        "success" => false,
        "message" => $th->getMessage()
    ]);
    logError("Error: " . $th->getMessage(), "ERROR");
}
