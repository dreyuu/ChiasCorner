<?php
include_once __DIR__ . '/../../connection.php';
include_once __DIR__ . '../../../components/pusher_helper.php';
include_once __DIR__ . '../../../components/system_log.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader
try {
    $ownerID = $_POST['owner_id'];
    $promo_id = $_POST['promoId'];
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
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    logError("Delete promo error: " . $e->getMessage(), "ERROR");
    http_response_code(500);  // Internal Server Error
}
