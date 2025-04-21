<?php
require '../../connection.php';

$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    echo json_encode(["error" => "Order ID is required"]);
    exit();
}

try {
    // Fetch order details
    $stmt = $connect->prepare("
        SELECT o.order_id, o.total_price, o.payment_status, o.paid_amount, o.discount_amount, o.vat_amount, o.vatable_amount,
                o.dine, u.name AS cashier_name
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        WHERE o.order_id = :order_id
    ");
    $stmt->execute([':order_id' => $order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(["error" => "Order not found"]);
        exit();
    }

    // Fetch ordered items
    $stmt = $connect->prepare("
        SELECT m.menu_id, m.name, oi.quantity, oi.price 
        FROM order_items oi
        JOIN menu m ON oi.menu_id = m.menu_id
        WHERE oi.order_id = :order_id
    ");
    $stmt->execute([':order_id' => $order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch consumed ingredients
    $stmt = $connect->prepare("
        SELECT i.ingredient_id, i.ingredient_name, i.unit, oi.consumed_quantity, oi.quantity_required, mi.ingredient_type
        FROM order_ingredients oi
        JOIN ingredients i ON oi.ingredient_id = i.ingredient_id
        JOIN menu_ingredients mi ON i.ingredient_id = mi.ingredient_id
        WHERE oi.order_id = :order_id 
    ");
    $stmt->execute([':order_id' => $order_id]);
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- PROMO HANDLING ---

    // Get today's date
    $today = date("Y-m-d");

    // Fetch active promotions valid today
    $stmt = $connect->prepare("
        SELECT * FROM promotions 
            WHERE status = 'active' 
            AND :today BETWEEN start_date AND end_date
    ");
    $stmt->execute([':today' => $today]);
    $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $applied_promos = [];
    $promo_discount_total = 0.00;

    foreach ($promotions as $promo) {
        $applies = false;

        // If applicable_menu_id is null, apply to all items
        if ($promo['applicable_menu_id'] === null) {
            $applies = true;
        } else {
            foreach ($items as $item) {
                if ($item['menu_id'] == $promo['applicable_menu_id']) {
                    $applies = true;
                    break;
                }
            }
        }

        if ($applies) {
            if ($promo['discount_type'] === 'fixed') {
                $promo_discount_total += floatval($promo['discount_value']);
            } elseif ($promo['discount_type'] === 'percentage') {
                $promo_discount_total += ($order['total_price'] * (floatval($promo['discount_value']) / 100));
            }

            $applied_promos[] = [
                'name' => $promo['name'],
                'type' => $promo['discount_type'],
                'value' => $promo['discount_value']
            ];
        }
    }

    // Final price after discount (but keep original in case needed)
    $final_total_price = $order['total_price'] - $promo_discount_total;

    echo json_encode([
        "order" => $order,
        "items" => $items,
        "ingredients" => $ingredients,
        "promotions" => $applied_promos,
        "promo_discount_total" => round($promo_discount_total, 2),
        "final_total_price" => round($final_total_price, 2)
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => "Error fetching order details: " . $e->getMessage()]);
}
?>
