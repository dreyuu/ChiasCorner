<?php
include_once __DIR__ . '/../../connection.php';

$sql = "SELECT * FROM ingredients";
$stmt = $connect->prepare($sql);
$stmt->execute();
$ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["success" => true, "ingredients" => $ingredients]);
