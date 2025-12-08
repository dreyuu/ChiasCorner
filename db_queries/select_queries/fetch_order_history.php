<?php
include_once __DIR__ . '/../../connection.php';
require __DIR__ . '/../../components/logger.php';  // Load logger

$inputData = json_decode(file_get_contents("php://input"), true);

// Pagination parameters
$page  = isset($inputData['page']) ? (int)$inputData['page'] : 1;
$limit = isset($inputData['limit']) ? (int)$inputData['limit'] : 10;
$offset = ($page - 1) * $limit;

try {
    // 1️⃣ Get total row count
    $countQuery = "SELECT COUNT(*) FROM order_history";
    $stmt = $connect->prepare($countQuery);
    $stmt->execute();
    $totalRows = (int)$stmt->fetchColumn();

    // 2️⃣ Fetch paginated orders
    $sql = "SELECT oh.order_id,
                   oh.items_ordered,
                   oh.total_price,
                   oh.discount_amount,
                   oh.paid_amount,
                   (oh.paid_amount - oh.total_price + oh.discount_amount) AS change_given,
                   oh.payment_status,
                   u.username AS cashier,
                   oh.order_date,
                   oh.archived_date
            FROM order_history oh
            LEFT JOIN users u ON oh.user_id = u.user_id
            ORDER BY oh.archived_date DESC
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
        'message' => "Error fetching orders: " . $e->getMessage()
    ]);

    logError("Database error: " . $e->getMessage(), "ERROR");
    http_response_code(500);
}
