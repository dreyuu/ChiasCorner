<?php
include '../../connection.php';

$dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : '';
$dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

$params = [];
$whereClauses = [];

// Apply date filter if provided
if (!empty($dateFrom) && !empty($dateTo)) {
    $whereClauses[] = "oh.order_date BETWEEN :dateFrom AND :dateTo";
    $params[':dateFrom'] = $dateFrom;
    $params[':dateTo'] = $dateTo;
}

// Apply category filter if provided
if (!empty($category) && $category !== 'all') {
    $whereClauses[] = "EXISTS (SELECT 1 FROM order_items oi 
                                JOIN menu m ON oi.menu_id = m.menu_id 
                                WHERE oi.order_id = oh.order_id 
                                AND m.category = :category)";
    $params[':category'] = $category;
}

// Build the sales and customers query
$query = "SELECT 
                COALESCE(SUM(oh.total_price), 0) AS total_sales, 
                COUNT(DISTINCT oh.order_id) AS customers_served,
                COALESCE(SUM(oh.vat_amount), 0) AS vat_amount
            FROM order_history oh
            WHERE oh.payment_status = 'paid'";

// Apply WHERE conditions if any exist
if (!empty($whereClauses)) {
    $query .= " AND " . implode(" AND ", $whereClauses);
}
$stmt = $connect->prepare($query);
$stmt->execute($params);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$vat_amount = $result['vat_amount'] ?? 0;
$Sales = $result['total_sales'] ?? 0;
$customersServed = $result['customers_served'] ?? 0;

$totalSales = $Sales - $vat_amount;

// Fetch Best Seller
$bestSellerQuery = "SELECT m.name 
    FROM order_items oi
    JOIN menu m ON oi.menu_id = m.menu_id
    JOIN order_history oh ON oi.order_id = oh.order_id
    WHERE oh.payment_status = 'paid'";

// Apply filters if any
if (!empty($whereClauses)) {
    $bestSellerQuery .= " AND " . implode(" AND ", $whereClauses);
}
$bestSellerQuery .= " GROUP BY oi.menu_id ORDER BY SUM(oi.quantity) DESC LIMIT 1";

$stmtBestSeller = $connect->prepare($bestSellerQuery);
$stmtBestSeller->execute($params);
$bestSellerResult = $stmtBestSeller->fetch(PDO::FETCH_ASSOC);
$bestSeller = $bestSellerResult['name'] ?? 'N/A';

// Return JSON Response
echo json_encode([
    'totalSales' => number_format($Sales, 2),
    'customersServed' => $customersServed,
    'bestSeller' => $bestSeller,
    'vat_amount' => number_format($vat_amount, 2),
    'net_sales' => number_format($totalSales, 2),
]);

?>
