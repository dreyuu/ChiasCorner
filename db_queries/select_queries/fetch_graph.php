<?php
include_once __DIR__ . '/../../connection.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader

$dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : '';
$dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : '';
$params = [];
$whereClauses = [];

try {
    // Apply date filters if provided
    if (!empty($dateFrom) && !empty($dateTo)) {
        $whereClauses[] = "oh.archived_date BETWEEN :dateFrom AND :dateTo"; // Reference archived_date
        $params[':dateFrom'] = $dateFrom;
        $params[':dateTo'] = $dateTo;
    }
    // Fetch Monthly Sales (from order_history)
    $query = "SELECT
                DATE_FORMAT(oh.order_date, '%Y-%m') AS sales_month,
                SUM(oh.total_price) AS total
            FROM
                order_history oh
            WHERE
                oh.payment_status = 'paid'";

    // Apply filters
    if (!empty($whereClauses)) {
        $query .= " AND " . implode(" AND ", $whereClauses);
    }

    $query .= " GROUP BY sales_month
            ORDER BY sales_month DESC";

    // Limit to 4 months if no filters
    if (empty($whereClauses)) {
        $query .= " LIMIT 4";
    }

    $stmt = $connect->prepare($query);
    $stmt->execute($params);
    $monthlySales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // Fetch Category Sales Breakdown (from order_history, payment status 'paid')
    $query2 = "SELECT m.category, SUM(oi.quantity * oi.price) AS total
            FROM order_history oh
            JOIN order_items oi ON oh.order_id = oi.order_id
            JOIN menu m ON oi.menu_id = m.menu_id
            WHERE oh.payment_status = 'paid'"; // Filter for paid orders

    if (!empty($whereClauses)) {
        $query2 .= " AND " . implode(" AND ", $whereClauses); // Apply additional filters (e.g., date range)
    }

    $query2 .= " GROUP BY m.category ORDER BY total DESC"; // Group by category and order by total sales

    $stmt2 = $connect->prepare($query2);
    $stmt2->execute($params);
    $categorySales = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    $stmt2->closeCursor(); // Close the cursor to allow the next query to execute

    // Fetch Top-Selling Menu Items (from order_history, payment status 'paid')
    $query3 = "SELECT m.name, SUM(oi.quantity) AS total_quantity
            FROM order_items oi
            JOIN menu m ON oi.menu_id = m.menu_id
            JOIN order_history oh ON oi.order_id = oh.order_id
            WHERE oh.payment_status = 'paid'"; // Filter for paid orders

    if (!empty($whereClauses)) {
        $query3 .= " AND " . implode(" AND ", $whereClauses); // Apply additional filters (e.g., date range)
    }

    $query3 .= " GROUP BY m.menu_id ORDER BY total_quantity DESC LIMIT 5"; // Get top 5 best-selling items

    $stmt3 = $connect->prepare($query3);
    $stmt3->execute($params);
    $topMenus = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    $stmt3->closeCursor(); // Close the cursor

    // Return JSON Response
    header('Content-Type: application/json');
    echo json_encode([
        "monthlySales" => $monthlySales ?? [],
        "categorySales" => $categorySales ?? [],
        "topMenus" => $topMenus ?? []
    ]);
} catch (\Throwable $th) {
    //throw $th;
    echo json_encode(["error" => "Error fetching graph data: " . $th->getMessage()]);
    logError("Error fetching graph data: " . $th->getMessage(), "ERROR");
    http_response_code(500);  // Internal Server Error
}
