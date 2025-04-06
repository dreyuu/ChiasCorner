<?php
include_once '../../connection.php';
session_start();

try {
    $user_id = $_SESSION["user_id"] ?? null;
    $items = json_decode($_POST['orders'], true);
    $discount = $_POST['discount_amount'] ?? 0.00;
    $dineType = $_POST['dine_type'] ?? 'Dine-In';

    if (empty($user_id)) {
        die(json_encode(["status" => "error", "message" => "User not logged in."]));
    }
    
    if (empty($items)) {
        die(json_encode(["status" => "error", "message" => "No items in order."]));
    }

    $connect->beginTransaction();

    // Step 1: Insert order
    $stmt = $connect->prepare("INSERT INTO orders (user_id, total_price, discount_amount, dine) VALUES (?, 0, ?, ?)");
    $stmt->execute([$user_id, $discount, $dineType]);
    $order_id = $connect->lastInsertId();

    $total_price = 0;

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
        $total_price += $subtotal;

        // Insert into order_items
        $stmt = $connect->prepare("INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $menu_id, $quantity, $price]);

        // Step 2: Insert ingredient tracking (new table)
        $stmt = $connect->prepare("SELECT ingredient_id, quantity_required FROM menu_ingredients WHERE menu_id = ?");
        $stmt->execute([$menu_id]);
        $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Menu ID: $menu_id | Ingredients: " . json_encode($ingredients));

        foreach ($ingredients as $ingredient) {
            $ingredient_id = $ingredient['ingredient_id'];
            $required_qty = $ingredient['quantity_required'] * $quantity;

            // Track ingredient consumption (without deducting inventory)
            $stmt = $connect->prepare("INSERT INTO order_ingredients (order_id, menu_id, ingredient_id, quantity_required, consumed_quantity) 
                                        VALUES (?, ?, ?, ?, NULL)");
            $stmt->execute([$order_id, $menu_id, $ingredient_id, $required_qty]);
        }
    }

    // Step 3: Update total price in orders
    $total_price -= $discount;
    $stmt = $connect->prepare("UPDATE orders SET total_price = ? WHERE order_id = ?");
    $stmt->execute([$total_price, $order_id]);

    $connect->commit();

    echo json_encode(["status" => "success", "order_id" => $order_id, "total_price" => $total_price]);

} catch (PDOException $e) {
    $connect->rollBack();
    die(json_encode(["status" => "error", "message" => $e->getMessage()]));
}
?>
