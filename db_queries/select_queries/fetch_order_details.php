<?php
require '../../connection.php';

$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    echo json_encode(["error" => "Order ID is required"]);
    exit();
}

try {
    // Fetch order details including dine type, payment info, and cashier name
    $stmt = $connect->prepare("
        SELECT o.order_id, o.total_price, o.payment_status, o.paid_amount, o.discount_amount, 
                o.dine, u.name AS cashier_name
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        WHERE o.order_id = :order_id
    ");
    $stmt->execute([':order_id' => $order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(["error" => "Order not found"]);
        exit();
    }

    // Fetch ordered items
    $stmt = $connect->prepare("
        SELECT m.name, oi.quantity, oi.price 
        FROM order_items oi
        JOIN menu m ON oi.menu_id = m.menu_id
        WHERE oi.order_id = :order_id
    ");
    $stmt->execute([':order_id' => $order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch only "unli" ingredients by joining `menu_ingredients`
    $stmt = $connect->prepare("
        SELECT i.ingredient_id, i.ingredient_name, i.unit, oi.consumed_quantity, oi.quantity_required, mi.ingredient_type
        FROM order_ingredients oi
        JOIN ingredients i ON oi.ingredient_id = i.ingredient_id
        JOIN menu_ingredients mi ON i.ingredient_id = mi.ingredient_id
        WHERE oi.order_id = :order_id 
    ");
    $stmt->execute([':order_id' => $order_id]);
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "order" => $order, 
        "items" => $items, 
        "ingredients" => $ingredients
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => "Error fetching order details: " . $e->getMessage()]);
}
?>
