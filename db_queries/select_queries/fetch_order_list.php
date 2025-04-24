<?php
include_once '../../connection.php';

try {
    // Query to get orders and order items
    $sql = "SELECT o.order_id, 
                GROUP_CONCAT(m.name SEPARATOR ', ') AS order_list,
                o.total_price,
                o.payment_status,
                o.paid_amount,
                o.discount_amount
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN menu m ON oi.menu_id = m.menu_id
            WHERE o.payment_status = 'pending'  -- ✅ Moved WHERE clause before GROUP BY
            GROUP BY o.order_id
            ORDER BY o.order_id DESC;
            ";

    $stmt = $connect->prepare($sql);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Send the fetched orders as JSON
    echo json_encode(['success' => true, 'orders' => $orders]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
