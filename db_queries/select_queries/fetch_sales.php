<?php
include_once __DIR__ . '/../../connection.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader
header('Content-Type: application/json');

$dateFrom = isset($_GET['from']) ? $_GET['from'] : null;
$dateTo = isset($_GET['to']) ? $_GET['to'] : null;

try {
    $whereClause = "WHERE oh.payment_status = 'paid'";
    $params = [];

    if ($dateFrom && $dateTo) {
        $whereClause .= " AND DATE(oh.order_date) BETWEEN :from AND :to";
        $params[':from'] = $dateFrom;
        $params[':to'] = $dateTo;
    }

    // ---------- TOTAL SALES ----------
    $sqlTotal = "SELECT IFNULL(SUM(oh.total_price), 0) AS total_sales
             FROM order_history oh
             $whereClause";
    $stmt = $connect->prepare($sqlTotal);
    $stmt->execute($params);
    $totalSales = $stmt->fetchColumn();

    // ---------- CUSTOMERS SERVED ----------
    $sqlCustomers = "SELECT COUNT(DISTINCT oh.order_id) AS customersServed
                 FROM order_history oh
                 $whereClause";
    $stmt = $connect->prepare($sqlCustomers);
    $stmt->execute($params);
    $customersServed = $stmt->fetchColumn();

    // ---------- TODAY / WEEK / MONTH SALES ----------
    function getSales($connect, $condition)
    {
        $sql = "SELECT IFNULL(SUM(total_price), 0) AS sales
            FROM order_history
            WHERE payment_status = 'paid' AND $condition";
        $stmt = $connect->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    $todaySales = getSales($connect, "DATE(order_date) = CURDATE()");
    $weekSales = getSales($connect, "YEARWEEK(order_date, 1) = YEARWEEK(CURDATE(), 1)");
    $monthSales = getSales($connect, "MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE())");

    if ($dateFrom && $dateTo) {
        $todaySales = $weekSales = $monthSales = 0;
    }

    // ---------- COMBINED STAFF SALES (TOTAL + TODAY) ----------
    $sqlStaffTotal = "SELECT u.user_id, u.name, IFNULL(SUM(oh.total_price), 0) AS total_sales
                  FROM order_history oh
                  JOIN users u ON oh.user_id = u.user_id
                  $whereClause
                  GROUP BY u.user_id
                  ORDER BY total_sales DESC";
    $stmt = $connect->prepare($sqlStaffTotal);
    $stmt->execute($params);
    $staffSalesTotal = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sqlStaffToday = "SELECT u.user_id, IFNULL(SUM(oh.total_price), 0) AS sales_today
                  FROM order_history oh
                  JOIN users u ON oh.user_id = u.user_id
                  WHERE oh.payment_status = 'paid' AND DATE(oh.order_date) = CURDATE()
                  GROUP BY u.user_id";
    $stmt = $connect->prepare($sqlStaffToday);
    $stmt->execute();
    $staffSalesToday = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Combine total sales and today sales
    $staffSales = [];
    foreach ($staffSalesTotal as $staff) {
        $today = 0;
        foreach ($staffSalesToday as $st) {
            if ($st['user_id'] == $staff['user_id']) {
                $today = $st['sales_today'];
                break;
            }
        }
        $staffSales[] = [
            'name' => $staff['name'],
            'salesToday' => number_format($today, 2),
            'totalSales' => number_format($staff['total_sales'], 2)
        ];
    }

    // ---------- TOTAL ITEMS SOLD ----------
    $sqlItems = "SELECT IFNULL(SUM(oi.quantity), 0) AS total_items
             FROM order_items oi
             JOIN order_history oh ON oi.order_id = oh.order_id
             $whereClause";
    $stmt = $connect->prepare($sqlItems);
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();

    // ---------- TOP 5 PRODUCTS ----------
    $sqlTopProducts = "SELECT m.name, SUM(oi.quantity) AS total_sold
                   FROM order_items oi
                   JOIN menu m ON oi.menu_id = m.menu_id
                   JOIN order_history oh ON oi.order_id = oh.order_id
                   $whereClause
                   GROUP BY m.menu_id
                   ORDER BY total_sold DESC
                   LIMIT 5";
    $stmt = $connect->prepare($sqlTopProducts);
    $stmt->execute($params);
    $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ---------- LEAST 5 PRODUCTS ----------
    $sqlLeastProducts = "SELECT m.name, SUM(oi.quantity) AS total_sold
                     FROM order_items oi
                     JOIN menu m ON oi.menu_id = m.menu_id
                     JOIN order_history oh ON oi.order_id = oh.order_id
                     $whereClause
                     GROUP BY m.menu_id
                     ORDER BY total_sold ASC
                     LIMIT 5";
    $stmt = $connect->prepare($sqlLeastProducts);
    $stmt->execute($params);
    $leastProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ---------- DAILY AVERAGE SALES ----------
    $sqlDays = "SELECT COUNT(DISTINCT DATE(oh.order_date)) AS days
            FROM order_history oh
            $whereClause";
    $stmt = $connect->prepare($sqlDays);
    $stmt->execute($params);
    $days = $stmt->fetchColumn();
    $avgDailySales = $days > 0 ? $totalSales / $days : 0;

    // ---------- AVERAGE SALE PER CUSTOMER ----------
    $avgPerCustomer = $customersServed > 0 ? $totalSales / $customersServed : 0;

    // ---------- HIGHEST / LOWEST SALES DAY ----------
    $sqlDaySales = "SELECT DATE(oh.order_date) AS date, SUM(oh.total_price) AS total
                FROM order_history oh
                $whereClause
                GROUP BY DATE(oh.order_date)
                ORDER BY total DESC";
    $stmt = $connect->prepare($sqlDaySales);
    $stmt->execute($params);
    $daySales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $highestSalesDay = $daySales[0]['date'] ?? null;
    $lowestSalesDay = $daySales ? end($daySales)['date'] : null;

    // ---------- TOP / LOWEST STAFF ----------
    $topStaff = $staffSales[0]['name'] ?? null;
    $lowestStaff = $staffSales ? end($staffSales)['name'] : null;

    // ---------- FINAL RESPONSE ----------
    $response = [
        'totalSales' => number_format($totalSales, 2),
        'customersServed' => intval($customersServed),
        'avgSalePerCustomer' => number_format($avgPerCustomer, 2),
        'avgDailySales' => number_format($avgDailySales, 2),
        'highestSalesDay' => $highestSalesDay,
        'lowestSalesDay' => $lowestSalesDay,
        'todaySales' => number_format($todaySales, 2),
        'weekSales' => number_format($weekSales, 2),
        'monthSales' => number_format($monthSales, 2),
        'totalItemsSold' => intval($totalItems),
        'staffSales' => $staffSales,
        'topStaff' => $topStaff,
        'lowestStaff' => $lowestStaff,
        'topProducts' => $topProducts,
        'leastProducts' => $leastProducts
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (\Throwable $th) {
    logError("Error fetching sales data: " . $th->getMessage(), "ERROR");
    http_response_code(500);
    echo json_encode(["error" => "An error occurred while fetching sales data."]);
}
