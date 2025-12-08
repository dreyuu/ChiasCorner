<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../connection.php";
require __DIR__ . '/../../components/logger.php';

$input = json_decode(file_get_contents("php://input"), true);

try {

    if (!isset($input['user_id'])) {
        echo json_encode([
            "success" => false,
            "message" => "User ID not provided."
        ]);
        exit;
    }

    $user_id = intval($input['user_id']);

    // Fetch PIN
    $stmt = $connect->prepare("SELECT auth_pin FROM users WHERE user_id = :id AND user_type = 'admin'");
    $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode([
            "success" => false,
            "message" => "Admin not found."
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "pin" => $row['auth_pin']
    ]);
} catch (Throwable $th) {
    echo json_encode([
        "success" => false,
        "message" => $th->getMessage()
    ]);
    logError("Error loading PIN: " . $th->getMessage(), "ERROR");
}
