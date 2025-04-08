<?php
include_once '../../connection.php';

try {
    $query = "UPDATE notifications SET status = 'read' WHERE status = 'unread'";
    $stmt = $connect->prepare($query);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Notifications updated to read"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
