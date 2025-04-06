<?php
include '../../connection.php'; // Ensure database connection

try {
    $order_id = $_GET['order_id'];

    // Prepare the SQL query using PDO
    $sql = "SELECT oi.menu_id, m.name, oi.quantity, m.price, o.dine
            FROM order_items oi
            JOIN menu m ON oi.menu_id = m.menu_id
            JOIN orders o ON oi.order_id = o.order_id
            WHERE oi.order_id = :order_id";

    $stmt = $connect->prepare($sql);
    $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);
    $stmt->execute();

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "items" => $items,
        "dineType" => $items[0]['dine_type'] ?? 'Dine-In'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>
