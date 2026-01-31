<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../../connection.php';

try {
    // Read POST JSON input
    $input = json_decode(file_get_contents("php://input"), true);
    $category = $input['action_category'] ?? null;
    $page = isset($input['page']) ? (int)$input['page'] : 1;
    $limit = 10; // rows per page
    $offset = ($page - 1) * $limit;

    // Get total rows for pagination
    if ($category) {
        $countStmt = $connect->prepare("
            SELECT COUNT(*) as total FROM system_logs
            WHERE action_category = :category
        ");
        $countStmt->execute([':category' => $category]);
    } else {
        $countStmt = $connect->prepare("SELECT COUNT(*) as total FROM system_logs");
        $countStmt->execute();
    }
    $totalRows = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Fetch logs with JOIN to get user names
    if ($category) {
        $stmt = $connect->prepare("
            SELECT sl.*, u.name AS user_name
            FROM system_logs sl
            LEFT JOIN users u ON sl.user_id = u.user_id
            WHERE sl.action_category = :category
            ORDER BY sl.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':category', $category, PDO::PARAM_STR);
    } else {
        $stmt = $connect->prepare("
            SELECT sl.*, u.name AS user_name
            FROM system_logs sl
            LEFT JOIN users u ON sl.user_id = u.user_id
            ORDER BY sl.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'logs' => $logs,
        'totalRows' => $totalRows
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
