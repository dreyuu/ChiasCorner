<?php
include_once __DIR__ . '/../../connection.php';
require __DIR__ . '/../../components/logger.php';
header('Content-Type: application/json');

$dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : '';
$dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : '';
$params = [];
$whereClauses = [];

// Always filter by paid status
$baseCondition = "oh.payment_status = 'paid'";

try {
    // 1. Build Dynamic Where Clause based on Date
    if (!empty($dateFrom) && !empty($dateTo)) {
        // CHANGED: Use DATE(oh.order_date) instead of archived_date
        $whereClauses[] = "DATE(oh.order_date) BETWEEN :dateFrom AND :dateTo";
        $params[':dateFrom'] = $dateFrom;
        $params[':dateTo'] = $dateTo;
    }

    // Helper to combine base condition and date filters
    function buildWhere($base, $clauses)
    {
        if (!empty($clauses)) {
            return "WHERE " . $base . " AND " . implode(" AND ", $clauses);
        }
        return "WHERE " . $base;
    }

    $fullWhere = buildWhere($baseCondition, $whereClauses);

    // --- 1. Monthly Sales ---
    $query = "SELECT
                DATE_FORMAT(oh.order_date, '%Y-%m') AS sales_month,
                SUM(oh.total_price) AS total
              FROM order_history oh
              $fullWhere
              GROUP BY sales_month
              ORDER BY sales_month ASC"; // ASC is better for charts (Jan -> Dec)

    // If no filter, show last 6 months
    if (empty($whereClauses)) {
        $query = "SELECT * FROM ($query LIMIT 6) as sub ORDER BY sales_month ASC";
    }

    $stmt = $connect->prepare($query);
    $stmt->execute($params);
    $monthlySales = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // --- 2. Category Sales ---
    $query2 = "SELECT m.category, SUM(oi.quantity * m.price) AS total
               FROM order_history oh
               JOIN order_items oi ON oh.order_id = oi.order_id
               JOIN menu m ON oi.menu_id = m.menu_id
               $fullWhere
               GROUP BY m.category
               ORDER BY total DESC";

    $stmt2 = $connect->prepare($query2);
    $stmt2->execute($params);
    $categorySales = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "monthlySales" => $monthlySales ?? [],
        "categorySales" => $categorySales ?? []
    ]);
} catch (\Throwable $th) {
    logError("Error fetching graph data: " . $th->getMessage(), "ERROR");
    http_response_code(500);
    echo json_encode(["error" => "Error fetching graph data"]);
}
