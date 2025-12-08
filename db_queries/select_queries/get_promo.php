<?php
include_once __DIR__ . '/../../connection.php';

$promo_id = $_GET['promo_id'];

$stmt = $connect->prepare("SELECT * FROM promotions WHERE promo_id = ?");
$stmt->execute([$promo_id]);

echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
