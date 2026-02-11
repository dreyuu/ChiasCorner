<?php
include_once __DIR__ . '/../../connection.php';
include_once __DIR__ . '/../../components/pusher_helper.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader

try {
    $items = json_decode($_POST['orders'] ?? '[]', true);
    $user_id = $items[0]['user_id'] ?? null;
    $dineType = $_POST['dine_type'] ?? 'Dine-In';
    $today = date("Y-m-d");

    if (empty($items)) {
        throw new Exception("No items in order.");
    }

    if (empty($user_id)) {
        throw new Exception("User not logged in.");
    }

    $connect->beginTransaction();

    // ✅ Step 1: Auto-deactivate expired promos
    $updateExpired = $connect->prepare("
        UPDATE promotions
        SET status = 'inactive'
        WHERE end_date < CURDATE()
        AND status = 'active'
    ");
    $updateExpired->execute();

    // ✅ (Optional) Auto-activate promos that start today
    $activateToday = $connect->prepare("
        UPDATE promotions
        SET status = 'active'
        WHERE start_date <= CURDATE()
        AND end_date >= CURDATE()
        AND status = 'inactive'
    ");
    $activateToday->execute();

    // ✅ Step 2: Insert order (placeholder totals)
    $stmt = $connect->prepare("INSERT INTO orders (user_id, total_price, discount_amount, dine, order_date)
                            VALUES (?, 0, 0, ?, ?)");
    $stmt->execute([$user_id, $dineType, localNow()]);

    $order_id = $connect->lastInsertId();

    $total_price = 0;
    $discount_total = 0;
    $applied_promos = [];

    // ✅ Step 3: Prepared statements for reuse
    $menuStmt = $connect->prepare("SELECT price FROM menu WHERE menu_id = ?");
    $promoStmt = $connect->prepare("
        SELECT *
        FROM promotions
        WHERE status = 'active'
        AND :today BETWEEN start_date AND end_date
        AND (applicable_menu_id = :menu_id OR applicable_menu_id IS NULL)
        ORDER BY applicable_menu_id DESC, discount_value DESC
        LIMIT 1
    ");
    $insertItem = $connect->prepare("
        INSERT INTO order_items (order_id, menu_id, quantity, price, discount, promo_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    // ✅ Step 4: Loop items
    foreach ($items as $item) {
        $menu_id = (int)$item['menu_id'];
        $quantity = max((int)$item['quantity'], 1);

        $menuStmt->execute([$menu_id]);
        $menu = $menuStmt->fetch(PDO::FETCH_ASSOC);
        if (!$menu) continue;

        $price = (float)$menu['price'];
        $subtotal = $price * $quantity;

        $promoStmt->execute([':today' => $today, ':menu_id' => $menu_id]);
        $promo = $promoStmt->fetch(PDO::FETCH_ASSOC);

        $discount = 0;
        $promo_id = null;

        if ($promo) {
            $promo_id = $promo['promo_id'];
            $discountValue = (float)$promo['discount_value'];

            if ($promo['discount_type'] === 'fixed') {
                $discount = $discountValue * $quantity;
            } elseif ($promo['discount_type'] === 'percentage') {
                $discount = ($subtotal * $discountValue) / 100;
            }

            // Cap the discount
            $discount = min($discount, $subtotal);
            $applied_promos[$promo_id] = true;
        }

        $discount_total += $discount;
        $total_price += $subtotal;

        $insertItem->execute([$order_id, $menu_id, $quantity, $price, $discount, $promo_id]);
    }

    // ✅ Step 5: Update order totals
    $final_total = max($total_price - $discount_total, 0);

    $updateOrder = $connect->prepare("
        UPDATE orders
        SET total_price = ?, discount_amount = ?, promo_applied = ?
        WHERE order_id = ?
    ");
    $promoList = implode(',', array_keys($applied_promos));
    $updateOrder->execute([$final_total, $discount_total, $promoList, $order_id]);

    $connect->commit();

    echo json_encode([
        "status" => "success",
        "order_id" => $order_id,
        "total_price" => $final_total,
        "total_discount" => $discount_total,
        "promos_used" => $promoList
    ]);

    PusherHelper::send("orders-channel", "modify-order", ["msg" => "Order placed successfully"]);
} catch (Exception $e) {
    if ($connect->inTransaction()) $connect->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    logError("Error placing order: " . $e->getMessage(), "ERROR");
    http_response_code(500);  // Internal Server Error
}
