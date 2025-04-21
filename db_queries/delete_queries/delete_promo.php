<?php
include_once '../../connection.php';

try {
    $promo_id = $_POST['promoId'];
    $stmt = $connect->prepare("DELETE FROM promotions WHERE promo_id = ?");
    $stmt->execute([$promo_id]);

    echo json_encode(["status" => "success"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
