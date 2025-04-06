<?php
include_once '../../connection.php';

try {
    $sql = "SELECT oh.order_id, 
                    oh.items_ordered, 
                    oh.total_price,
                    oh.discount_amount,
                    oh.paid_amount,
                    (oh.paid_amount - oh.total_price + oh.discount_amount) AS change_given,
                    oh.payment_status,
                    oh.discount_amount,
                    u.username AS cashier,
                    oh.order_date,
                    oh.archived_date
            FROM order_history oh
            LEFT JOIN users u ON oh.user_id = u.user_id
            ORDER BY oh.archived_date DESC";

    $stmt = $connect->prepare($sql);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($orders);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
