<?php
include_once __DIR__ . '/../../connection.php';

$inputData = json_decode(file_get_contents("php://input"), true);

// Pagination parameters
$page  = isset($inputData['page']) ? (int)$inputData['page'] : 1;
$limit = isset($inputData['limit']) ? (int)$inputData['limit'] : 10;
$offset = ($page - 1) * $limit;

try {
    // 1️⃣ Get total pending orders count
    $countQuery = "SELECT COUNT(*)
                   FROM orders o
                   WHERE o.payment_status = 'pending'";
    $stmt = $connect->prepare($countQuery);
    $stmt->execute();
    $totalRows = (int)$stmt->fetchColumn();

    // 2️⃣ Fetch paginated pending orders
    $sql = "SELECT o.order_id,
                   GROUP_CONCAT(m.name SEPARATOR ', ') AS order_list,
                   o.total_price,
                   o.dine,
                   o.payment_status,
                   o.paid_amount,
                   o.discount_amount
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN menu m ON oi.menu_id = m.menu_id
            WHERE o.payment_status = 'pending'
            GROUP BY o.order_id
            ORDER BY o.order_id DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $connect->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'totalRows' => $totalRows,
        'page' => $page,
        'limit' => $limit
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => "Error fetching pending orders: " . $e->getMessage()
    ]);
}
