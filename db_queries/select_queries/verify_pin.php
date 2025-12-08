<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../connection.php";
require __DIR__ . '/../../components/logger.php';

$input = json_decode(file_get_contents("php://input"), true);

try {
    if (!isset($input['pin'])) {
        echo json_encode(["success" => false, "message" => "PIN not provided."]);
        exit;
    }

    $pin = $input['pin'];

    $stmt = $connect->prepare("SELECT user_id FROM users WHERE auth_pin = :pin AND user_type = 'admin'");
    $stmt->bindValue(':pin', $pin, PDO::PARAM_STR);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid PIN"]);
    }
} catch (Throwable $th) {
    echo json_encode(["success" => false, "message" => $th->getMessage()]);
    logError("PIN Verify Error: " . $th->getMessage(), "ERROR");
}
