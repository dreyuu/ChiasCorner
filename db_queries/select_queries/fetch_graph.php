<?php
include '../../connection.php';

$dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : '';
$dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : '';
$params = [];
$whereClauses = [];

// Apply date filters if provided
if (!empty($dateFrom) && !empty($dateTo)) {
    $whereClauses[] = "o.order_date BETWEEN :dateFrom AND :dateTo";
    $params[':dateFrom'] = $dateFrom;
    $params[':dateTo'] = $dateTo;
}

// Fetch Monthly Sales
$query = "SELECT MONTH(o.order_date) AS month, SUM(o.total_price) AS total
          FROM orders o";

if (!empty($whereClauses)) {
    $query .= " WHERE " . implode(" AND ", $whereClauses);
}
$query .= " GROUP BY MONTH(o.order_date) ORDER BY MONTH(o.order_date)";

$stmt = $connect->prepare($query);
$stmt->execute($params);
$monthlySales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Category Sales Breakdown
$query2 = "SELECT m.category, SUM(oi.quantity * oi.price) AS total
           FROM orders o
           JOIN order_items oi ON o.order_id = oi.order_id
           JOIN menu m ON oi.menu_id = m.menu_id";

if (!empty($whereClauses)) {
    $query2 .= " WHERE " . implode(" AND ", $whereClauses);
}
$query2 .= " GROUP BY m.category ORDER BY total DESC";

$stmt2 = $connect->prepare($query2);
$stmt2->execute($params);
$categorySales = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Fetch Top-Selling Menu Items
$query3 = "SELECT m.name, SUM(oi.quantity) AS total_quantity
           FROM order_items oi
           JOIN menu m ON oi.menu_id = m.menu_id
           JOIN orders o ON oi.order_id = o.order_id";

if (!empty($whereClauses)) {
    $query3 .= " WHERE " . implode(" AND ", $whereClauses);
}
$query3 .= " GROUP BY m.menu_id ORDER BY total_quantity DESC LIMIT 5"; // Top 5 best-selling items

$stmt3 = $connect->prepare($query3);
$stmt3->execute($params);
$topMenus = $stmt3->fetchAll(PDO::FETCH_ASSOC);

// Return JSON Response
header('Content-Type: application/json');
echo json_encode([
    "monthlySales" => $monthlySales ?? [],
    "categorySales" => $categorySales ?? [],
    "topMenus" => $topMenus ?? []
]);
?>
