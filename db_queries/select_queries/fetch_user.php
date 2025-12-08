<?php
include_once __DIR__ . '/../../connection.php';
require __DIR__ . '/../../components/logger.php';

$inputData = json_decode(file_get_contents("php://input"), true);

// Pagination parameters
$page  = isset($inputData['page']) ? (int)$inputData['page'] : 1;
$limit = isset($inputData['limit']) ? (int)$inputData['limit'] : 10;
$offset = ($page - 1) * $limit;

try {
    // 1️⃣ Get total row count
    $countQuery = "SELECT COUNT(*) FROM users WHERE user_type != 'dev'";
    $stmt = $connect->prepare($countQuery);
    $stmt->execute();
    $totalRows = (int)$stmt->fetchColumn();

    // 2️⃣ Fetch paginated rows
    $query = "SELECT user_id, name, username, email, user_type, status, auth_pin, date_created
              FROM users
              WHERE user_type != 'dev'
              ORDER BY user_id DESC
              LIMIT :limit OFFSET :offset";

    $stmt = $connect->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $userData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $userData,
        'totalRows' => $totalRows,
        'page' => $page,
        'limit' => $limit
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching data: ' . $e->getMessage()
    ]);

    logError("Database error: " . $e->getMessage(), "ERROR");
    http_response_code(500);
}
