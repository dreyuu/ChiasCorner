<?php
include_once __DIR__ . '/../../connection.php';
require __DIR__ . '/../../components/logger.php';

$inputData = json_decode(file_get_contents("php://input"), true);

// Get status filter
$status = isset($inputData['status']) ? $inputData['status'] : 'active';

// Pagination parameters
$page  = isset($inputData['page']) ? (int)$inputData['page'] : 1;
$limit = isset($inputData['limit']) ? (int)$inputData['limit'] : 10;
$offset = ($page - 1) * $limit;

try {
    // 1️⃣ Auto-update expired promos
    $update = $connect->prepare("UPDATE promotions
                                SET status = 'inactive'
                                WHERE end_date < CURDATE()
                                AND status = 'active'");
    $update->execute();

    // 2️⃣ Count total promos for this status
    $countQuery = "SELECT COUNT(*) FROM promotions WHERE status = :status";
    $stmt = $connect->prepare($countQuery);
    $stmt->bindParam(":status", $status);
    $stmt->execute();
    $totalRows = (int)$stmt->fetchColumn();

    // 3️⃣ Fetch paginated promos
    $sql = "SELECT p.*, COALESCE(m.name, 'All') AS applicable_menu
            FROM promotions p
            LEFT JOIN menu m ON p.applicable_menu_id = m.menu_id
            WHERE p.status = :status
            ORDER BY p.start_date ASC
            LIMIT :limit OFFSET :offset";

    $stmt = $connect->prepare($sql);
    $stmt->bindParam(":status", $status);
    $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->execute();

    $promos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4️⃣ Return JSON with promos + totalRows + page info
    echo json_encode([
        "success" => true,
        "promos" => $promos,
        "totalRows" => $totalRows,
        "page" => $page,
        "limit" => $limit
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
    logError("Error fetching promotions: " . $e->getMessage(), "ERROR");
    http_response_code(500);
}
