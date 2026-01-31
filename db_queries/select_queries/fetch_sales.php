<?php
include_once __DIR__ . '/../../connection.php';
// require __DIR__ . '/../../components/logger.php';
header('Content-Type: application/json');

$dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : '';
$dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : '';

$params = [];
$whereClause = "WHERE oh.payment_status = 'paid'";

if (!empty($dateFrom) && !empty($dateTo)) {
    $whereClause .= " AND DATE(oh.order_date) BETWEEN :dateFrom AND :dateTo";
    $params[':dateFrom'] = $dateFrom;
    $params[':dateTo'] = $dateTo;
}

try {
    // 1. Total Sales
    $stmt = $connect->prepare("SELECT IFNULL(SUM(total_price), 0) FROM order_history oh $whereClause");
    $stmt->execute($params);
    $totalSales = $stmt->fetchColumn();

    // 2. Customers Served
    $stmt = $connect->prepare("SELECT COUNT(DISTINCT oh.order_id) FROM order_history oh $whereClause");
    $stmt->execute($params);
    $customersServed = $stmt->fetchColumn();

    // 3. Total Items Sold
    $stmt = $connect->prepare("SELECT IFNULL(SUM(oi.quantity), 0) FROM order_items oi JOIN order_history oh ON oi.order_id = oh.order_id $whereClause");
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();

    // 4. Top Products (For List)
    $sqlTop = "SELECT m.name, SUM(oi.quantity) AS total_sold
               FROM order_items oi JOIN menu m ON oi.menu_id = m.menu_id
               JOIN order_history oh ON oi.order_id = oh.order_id
               $whereClause
               GROUP BY m.menu_id ORDER BY total_sold DESC LIMIT 5";
    $stmt = $connect->prepare($sqlTop);
    $stmt->execute($params);
    $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Least Products (For List)
    $sqlLeast = "SELECT m.name, SUM(oi.quantity) AS total_sold
                 FROM order_items oi JOIN menu m ON oi.menu_id = m.menu_id
                 JOIN order_history oh ON oi.order_id = oh.order_id
                 $whereClause
                 GROUP BY m.menu_id ORDER BY total_sold ASC LIMIT 5";
    $stmt = $connect->prepare($sqlLeast);
    $stmt->execute($params);
    $leastProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /// 6. Staff Sales (For Table)
    // Modified to include subqueries for Today and Month specific to each user
    $sqlStaff = "SELECT
                    u.name,
                    IFNULL(SUM(oh.total_price), 0) as total_sales,

                    -- Subquery for Sales Today
                    (SELECT IFNULL(SUM(total_price), 0)
                     FROM order_history
                     WHERE user_id = u.user_id
                     AND payment_status = 'paid'
                     AND DATE(order_date) = CURDATE()) as sales_today,

                    -- Subquery for Sales This Month
                    (SELECT IFNULL(SUM(total_price), 0)
                     FROM order_history
                     WHERE user_id = u.user_id
                     AND payment_status = 'paid'
                     AND MONTH(order_date) = MONTH(CURDATE())
                     AND YEAR(order_date) = YEAR(CURDATE())) as sales_month

                 FROM order_history oh
                 JOIN users u ON oh.user_id = u.user_id
                 $whereClause
                 GROUP BY u.user_id
                 ORDER BY total_sales DESC";

    $stmt = $connect->prepare($sqlStaff);
    $stmt->execute($params);
    $staffSales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 7. Averages & Extras
    $avgPerCustomer = $customersServed > 0 ? $totalSales / $customersServed : 0;

    // Calculate Days in range for Avg Daily Sales
    $stmt = $connect->prepare("SELECT COUNT(DISTINCT DATE(order_date)) FROM order_history oh $whereClause");
    $stmt->execute($params);
    $activeDays = $stmt->fetchColumn();
    $avgDailySales = $activeDays > 0 ? $totalSales / $activeDays : 0;

    $bestSeller = !empty($topProducts) ? $topProducts[0]['name'] : "N/A";
    $topStaff = !empty($staffSales) ? $staffSales[0]['name'] : "N/A";
    $lowestStaff = !empty($staffSales) ? end($staffSales)['name'] : "N/A";

    // 8. Static Data (Today/Week/Month) - Usually these stay real-time regardless of filter
    // If you want these filtered, use $totalSales logic. I will leave them as real-time context.
    $stmt = $connect->prepare("SELECT IFNULL(SUM(total_price), 0) FROM order_history WHERE payment_status='paid' AND DATE(order_date) = CURDATE()");
    $stmt->execute();
    $todaySales = $stmt->fetchColumn();

    $stmt = $connect->prepare("SELECT IFNULL(SUM(total_price), 0) FROM order_history WHERE payment_status='paid' AND YEARWEEK(order_date, 1) = YEARWEEK(CURDATE(), 1)");
    $stmt->execute();
    $weekSales = $stmt->fetchColumn();

    $stmt = $connect->prepare("SELECT IFNULL(SUM(total_price), 0) FROM order_history WHERE payment_status='paid' AND MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE())");
    $stmt->execute();
    $monthSales = $stmt->fetchColumn();

    echo json_encode([
        'totalSales' => number_format($totalSales, 2),
        'customersServed' => $customersServed,
        'avgSalePerCustomer' => number_format($avgPerCustomer, 2),
        'avgDailySales' => number_format($avgDailySales, 2),
        'bestSeller' => $bestSeller,
        'totalItemsSold' => $totalItems,
        'todaySales' => number_format($todaySales, 2),
        'weekSales' => number_format($weekSales, 2),
        'monthSales' => number_format($monthSales, 2),
        'topStaff' => $topStaff,
        'lowestStaff' => $lowestStaff,
        'topProducts' => $topProducts,
        'leastProducts' => $leastProducts,
        'staffSales' => $staffSales
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
