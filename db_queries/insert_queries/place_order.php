<?php
include_once '../../connection.php';

try {
    $items = json_decode($_POST['orders'], true);
    $user_id = $items[0]['user_id'] ?? null;
    $dineType = $_POST['dine_type'] ?? 'Dine-In';
    $today = date("Y-m-d");

    if (empty($items)) {
        die(json_encode(["status" => "error", "message" => "No items in order."]));
    }
    
    if (empty($user_id)) {
        die(json_encode(["status" => "error", "message" => "User not logged in."]));
    }

    $connect->beginTransaction();

    // Step 1: Insert order with placeholder for total and discount
    $stmt = $connect->prepare("INSERT INTO orders (user_id, total_price, discount_amount, dine) VALUES (?, 0, 0, ?)");
    $stmt->execute([$user_id, $dineType]);
    $order_id = $connect->lastInsertId();

    $total_price = 0;
    $discount_total = 0;

    foreach ($items as $item) {
        $menu_id = $item['menu_id'];
        $quantity = $item['quantity'];

        // Get menu price
        $stmt = $connect->prepare("SELECT price FROM menu WHERE menu_id = ?");
        $stmt->execute([$menu_id]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$menu) continue;

        $price = $menu['price'];
        $subtotal = $price * $quantity;

        // Get applicable promotion
        $stmt = $connect->prepare("
            SELECT * FROM promotions 
            WHERE status = 'active' 
            AND :today BETWEEN start_date AND end_date 
            AND (applicable_menu_id = :menu_id OR applicable_menu_id IS NULL)
            ORDER BY applicable_menu_id DESC LIMIT 1
        ");
        $stmt->execute([':today' => $today, ':menu_id' => $menu_id]);
        $promo = $stmt->fetch(PDO::FETCH_ASSOC);

        $discount = 0;
        if ($promo) {
            if ($promo['discount_type'] === 'fixed') {
                $discount = $promo['discount_value'] * $quantity;
            } elseif ($promo['discount_type'] === 'percentage') {
                $discount = ($subtotal * $promo['discount_value']) / 100;
            }
        }

        $discount_total += $discount;
        $total_price += $subtotal;

        // Insert into order_items
        $stmt = $connect->prepare("INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $menu_id, $quantity, $price]);

        // Insert ingredient tracking
        $stmt = $connect->prepare("SELECT ingredient_id, quantity_required FROM menu_ingredients WHERE menu_id = ?");
        $stmt->execute([$menu_id]);
        $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Menu ID: $menu_id | Ingredients: " . json_encode($ingredients));

        foreach ($ingredients as $ingredient) {
            $ingredient_id = $ingredient['ingredient_id'];
            $required_qty = $ingredient['quantity_required'] * $quantity;

            $stmt = $connect->prepare("INSERT INTO order_ingredients (order_id, menu_id, ingredient_id, quantity_required, consumed_quantity) 
                                        VALUES (?, ?, ?, ?, NULL)");
            $stmt->execute([$order_id, $menu_id, $ingredient_id, $required_qty]);
        }
    }

    // Step 3: Update total and discount in orders
    $final_total = $total_price - $discount_total;
    $stmt = $connect->prepare("UPDATE orders SET total_price = ?, discount_amount = ? WHERE order_id = ?");
    $stmt->execute([$final_total, $discount_total, $order_id]);

    $connect->commit();

    echo json_encode([
        "status" => "success",
        "order_id" => $order_id,
        "total_price" => $final_total,
        "total_discount" => $discount_total
    ]);
} catch (PDOException $e) {
    $connect->rollBack();
    die(json_encode(["status" => "error", "message" => $e->getMessage()]));
}
