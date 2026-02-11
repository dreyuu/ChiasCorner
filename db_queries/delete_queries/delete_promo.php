<?php
include_once __DIR__ . '/../../connection.php';
include_once __DIR__ . '/../../components/pusher_helper.php';
include_once __DIR__ . '/../../components/system_log.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $ownerID = $input['owner_id'] ?? null;
    $promo_id = $input['promoId'] ?? null;

    if (!$promo_id || !$ownerID) {
        throw new Exception("Missing promo_id or owner_id");
    }

    // Fetch promo name for logging
    $stmtName = $connect->prepare("SELECT name FROM promotions WHERE promo_id = ?");
    $stmtName->execute([$promo_id]);
    $promo_name = $stmtName->fetchColumn() ?: 'Unknown';

    // Delete promo
    $stmt = $connect->prepare("DELETE FROM promotions WHERE promo_id = ?");
    $stmt->execute([$promo_id]);

    echo json_encode(["status" => "success"]);

    PusherHelper::send('promo-channel', 'modify-promo', ['msg' => 'Promotion deleted successfully']);

    logAction(
        $connect,
        $ownerID,
        'PROMO',
        'PROMO_REMOVE',
        "PROMO $promo_name deleted",
        $promo_id
    );
} catch (Throwable $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    error_log("Delete promo error: " . $e->getMessage());
    http_response_code(500);
}
