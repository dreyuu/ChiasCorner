<?php
// fetch_dashboard_data.php
// Returns JSON for dashboard. Admins see global stats, employees see personal data.
include_once __DIR__ . '/../../connection.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader
header('Content-Type: application/json; charset=utf-8');

try {
    $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    $userType = isset($_GET['user_type']) ? $_GET['user_type'] : 'employee';

    if ($userType === 'admin') {
        // ---------- TOTAL SALES & CUSTOMERS ----------
        $sql = "SELECT IFNULL(SUM(oh.total_price),0) AS total_sales,
                       COUNT(DISTINCT oh.order_id) AS customers_served
                FROM order_history oh
                WHERE oh.payment_status = 'paid'";
        $stmt = $connect->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalSales = $row['total_sales'] ?? 0;
        $customersServed = intval($row['customers_served'] ?? 0);

        // ---------- TODAY / WEEK / MONTH ----------
        $stmt = $connect->prepare("SELECT IFNULL(SUM(total_price),0) FROM order_history WHERE payment_status='paid' AND DATE(order_date)=CURDATE()");
        $stmt->execute();
        $today = $stmt->fetchColumn();

        $stmt = $connect->prepare("SELECT IFNULL(SUM(total_price),0) FROM order_history WHERE payment_status='paid' AND YEARWEEK(order_date,1)=YEARWEEK(CURDATE(),1)");
        $stmt->execute();
        $week = $stmt->fetchColumn();

        $stmt = $connect->prepare("SELECT IFNULL(SUM(total_price),0) FROM order_history WHERE payment_status='paid' AND MONTH(order_date)=MONTH(CURDATE()) AND YEAR(order_date)=YEAR(CURDATE())");
        $stmt->execute();
        $month = $stmt->fetchColumn();

        // ---------- STAFF SALES ----------
        $sqlStaffTotal = "SELECT u.user_id, u.name, IFNULL(SUM(oh.total_price),0) AS total_sales
                          FROM order_history oh
                          JOIN users u ON oh.user_id = u.user_id
                          WHERE oh.payment_status='paid'
                          GROUP BY u.user_id ORDER BY total_sales DESC";
        $stmt = $connect->prepare($sqlStaffTotal);
        $stmt->execute();
        $staffTotal = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlStaffToday = "SELECT u.user_id, IFNULL(SUM(oh.total_price),0) AS sales_today
                          FROM order_history oh
                          JOIN users u ON oh.user_id = u.user_id
                          WHERE oh.payment_status='paid' AND DATE(oh.order_date)=CURDATE()
                          GROUP BY u.user_id";
        $stmt = $connect->prepare($sqlStaffToday);
        $stmt->execute();
        $staffToday = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $staffSales = [];
        foreach ($staffTotal as $s) {
            $todayVal = 0;
            foreach ($staffToday as $st) {
                if ($st['user_id'] == $s['user_id']) {
                    $todayVal = $st['sales_today'];
                    break;
                }
            }
            $staffSales[] = [
                'user_id' => intval($s['user_id']),
                'name' => $s['name'],
                'salesToday' => number_format($todayVal, 2),
                'totalSales' => number_format($s['total_sales'], 2)
            ];
        }

        // ---------- TOTAL ITEMS SOLD ----------
        $sqlItems = "SELECT IFNULL(SUM(oi.quantity),0) AS total_items
                     FROM order_items oi
                     JOIN order_history oh ON oi.order_id = oh.order_id
                     WHERE oh.payment_status='paid'";
        $stmt = $connect->prepare($sqlItems);
        $stmt->execute();
        $totalItems = $stmt->fetchColumn();

        // ---------- TOP PRODUCTS ----------
        $sqlTop = "SELECT m.name, SUM(oi.quantity) AS total_sold
                   FROM order_items oi
                   JOIN menu m ON oi.menu_id = m.menu_id
                   JOIN order_history oh ON oi.order_id = oh.order_id
                   WHERE oh.payment_status='paid'
                   GROUP BY m.menu_id ORDER BY total_sold DESC LIMIT 5";
        $stmt = $connect->prepare($sqlTop);
        $stmt->execute();
        $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ---------- LEAST PRODUCTS ----------
        $sqlLeast = "SELECT m.name, SUM(oi.quantity) AS total_sold
                     FROM order_items oi
                     JOIN menu m ON oi.menu_id = m.menu_id
                     JOIN order_history oh ON oi.order_id = oh.order_id
                     WHERE oh.payment_status='paid'
                     GROUP BY m.menu_id ORDER BY total_sold ASC LIMIT 5";
        $stmt = $connect->prepare($sqlLeast);
        $stmt->execute();
        $leastProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ---------- CATEGORY SALES ----------
        $sqlCat = "SELECT m.category, IFNULL(SUM(oi.quantity * oi.price),0) AS total
                   FROM order_items oi
                   JOIN menu m ON oi.menu_id = m.menu_id
                   JOIN order_history oh ON oi.order_id = oh.order_id
                   WHERE oh.payment_status='paid'
                   GROUP BY m.category";
        $stmt = $connect->prepare($sqlCat);
        $stmt->execute();
        $categorySales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ---------- MONTHLY SALES ----------
        $sqlMonths = "SELECT YEAR(oh.order_date) AS yr, MONTH(oh.order_date) AS mo,
                             IFNULL(SUM(oh.total_price),0) AS total
                      FROM order_history oh
                      WHERE oh.payment_status='paid'
                      GROUP BY YEAR(oh.order_date), MONTH(oh.order_date)
                      ORDER BY YEAR(oh.order_date) DESC, MONTH(oh.order_date) DESC
                      LIMIT 6";
        $stmt = $connect->prepare($sqlMonths);
        $stmt->execute();
        $monthlySales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ---------- BEST SELLER ----------
        $bestSeller = $topProducts[0]['name'] ?? null;

        echo json_encode([
            'role' => 'admin',
            'totalSales' => number_format($totalSales, 2),
            'customersServed' => $customersServed,
            'todaySales' => number_format($today, 2),
            'weekSales' => number_format($week, 2),
            'monthSales' => number_format($month, 2),
            'totalItemsSold' => intval($totalItems),
            'staffSales' => $staffSales,
            'topProducts' => $topProducts,
            'leastProducts' => $leastProducts,
            'categorySales' => $categorySales,
            'monthlySales' => $monthlySales,
            'bestSeller' => $bestSeller
        ], JSON_PRETTY_PRINT);
        exit;
    } else {
        // ---------- EMPLOYEE ----------
        $stmt = $connect->prepare("SELECT user_id, name, username, email, user_type, DATE_FORMAT(date_created,'%Y-%m-%d') AS date_created FROM users WHERE user_id = :user_id LIMIT 1");
        $stmt->execute([':user_id' => $userId]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $connect->prepare("SELECT promo_id, name, discount_type, discount_value, start_date, end_date
                                   FROM promotions
                                   WHERE status='active' AND CURDATE() BETWEEN start_date AND end_date");
        $stmt->execute();
        $promos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $connect->prepare("SELECT oh.order_id, oh.total_price, oh.dine, oh.items_ordered, oh.order_date
                                   FROM order_history oh
                                   WHERE oh.user_id = :user_id AND oh.payment_status = 'paid' AND DATE(oh.order_date) = CURDATE()
                                   ORDER BY oh.order_date DESC");
        $stmt->execute([':user_id' => $userId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $completedCount = count($orders);
        $personalTotalToday = array_sum(array_column($orders, 'total_price'));

        $stmt = $connect->prepare("SELECT IFNULL(SUM(total_price),0) FROM order_history WHERE user_id = :user_id AND payment_status = 'paid' AND YEARWEEK(order_date,1)=YEARWEEK(CURDATE(),1)");
        $stmt->execute([':user_id' => $userId]);
        $personalWeek = $stmt->fetchColumn();

        $stmt = $connect->prepare("SELECT IFNULL(SUM(total_price),0) FROM order_history WHERE user_id = :user_id AND payment_status = 'paid' AND MONTH(order_date)=MONTH(CURDATE()) AND YEAR(order_date)=YEAR(CURDATE())");
        $stmt->execute([':user_id' => $userId]);
        $personalMonth = $stmt->fetchColumn();

        echo json_encode([
            'role' => 'employee',
            'employee' => $employee,
            'activePromotions' => $promos,
            'ordersToday' => $orders,
            'ordersServedToday' => intval($completedCount),
            'personalSalesToday' => number_format($personalTotalToday, 2),
            'personalSalesWeek' => number_format($personalWeek, 2),
            'personalSalesMonth' => number_format($personalMonth, 2)
        ], JSON_PRETTY_PRINT);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
    logError("Error fetching dashboard data: " . $e->getMessage(), "ERROR");
}
