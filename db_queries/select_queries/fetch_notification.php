<?php
include_once __DIR__ . '/../../connection.php';

try {
    $query = "SELECT * FROM notifications ORDER BY created_at DESC";
    $stmt = $connect->prepare($query);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "notifications" => $notifications]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error"]);
}
