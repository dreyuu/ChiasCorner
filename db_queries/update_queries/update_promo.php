<?php
include_once __DIR__ . '/../../connection.php';
include_once __DIR__ . '../../../components/pusher_helper.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader

try {
    $promo_id = $_POST['promoId'];
    $name = $_POST['promoName'];
    $type = $_POST['discount_type'];
    $value = $_POST['discount_value'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $menu_id = $_POST['applicable_menu'] === '' ? null : $_POST['applicable_menu'];

    $stmt = $connect->prepare("
        UPDATE promotions SET
            name = ?,
            discount_type = ?,
            discount_value = ?,
            start_date = ?,
            end_date = ?,
            applicable_menu_id = ?
        WHERE promo_id = ?
    ");
    $stmt->execute([$name, $type, $value, $start, $end, $menu_id, $promo_id]);

    echo json_encode(["status" => "success"]);

    PusherHelper::send('promo-channel', 'modify-promo', ['msg' => 'Promotion updated successfully']);
} catch (PDOException $e) {
    logError("Update promo error: " . $e->getMessage(), "ERROR");
    http_response_code(500);  // Internal Server Error
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
