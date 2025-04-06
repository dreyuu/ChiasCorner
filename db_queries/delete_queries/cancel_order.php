<?php
include_once '../../connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $order_id = $_POST['order_id'] ?? null;

        if (!$order_id) {
            die(json_encode(["status" => "error", "message" => "Invalid order ID."]));
        }

        $connect->beginTransaction();

        // Step 1: Fetch order details
        $stmt = $connect->prepare("SELECT * FROM orders WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            die(json_encode(["status" => "error", "message" => "Order not found."]));
        }

        // Step 2: Fetch order items
        $stmt = $connect->prepare("
            SELECT GROUP_CONCAT(m.name ORDER BY oi.order_item_id SEPARATOR ', ') AS items_ordered
            FROM order_items oi
            JOIN menu m ON oi.menu_id = m.menu_id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $order_items = $stmt->fetch(PDO::FETCH_ASSOC);
        $items_ordered = $order_items['items_ordered'] ?? 'No items';

        // Step 3: Insert order into order_history
        $stmt = $connect->prepare("
            INSERT INTO order_history (order_id, user_id, order_date, total_price, dine, payment_status, 
                                        paid_amount, discount_amount, items_ordered, archived_date) 
            VALUES (?, ?, ?, ?, ?, 'cancelled', ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $order['order_id'], 
            $order['user_id'], 
            $order['order_date'], 
            $order['total_price'], 
            $order['dine'] ?? 'Take-Out', // Default to 'Take-Out' if null
            $order['paid_amount'] ?? 0.00, 
            $order['discount_amount'] ?? 0.00,
            $items_ordered
        ]);

        // ** FIX: Delete order_ingredients first since it has a foreign key to orders **
        $stmt = $connect->prepare("
            DELETE FROM order_ingredients 
            WHERE order_id = ?
        ");
        $stmt->execute([$order_id]);

        // Then delete order_items
        $stmt = $connect->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->execute([$order_id]);

        // Finally, delete the order
        $stmt = $connect->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt->execute([$order_id]);

        $connect->commit();

        echo json_encode(["status" => "success", "message" => "Order cancelled and archived successfully."]);
    } catch (PDOException $e) {
        $connect->rollBack();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>
