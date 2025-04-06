<?php
include_once '../../connection.php';
session_start();

header('Content-Type: application/json');
// Decode JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    die(json_encode(["status" => "error", "message" => "Invalid JSON input."]));
}

try {
    $user_id = $_SESSION["user_id"] ?? null;
    $order_id = $data['order_id'] ?? null;
    $updatedItems = $data['updatedItems'] ?? [];
    $removedItems = $data['removedItems'] ?? [];

    if (empty($user_id) || empty($order_id)) {
        die(json_encode(["status" => "error", "message" => "Invalid user or order ID."]));
    }

    $connect->beginTransaction();

    $total_price = 0;

    // 1. Update existing items
    foreach ($updatedItems as $item) {
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

        // Check if order item exists
        $stmt = $connect->prepare("SELECT order_item_id FROM order_items WHERE order_id = ? AND menu_id = ?");
        $stmt->execute([$order_id, $menu_id]);
        $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingItem) {
            // Update existing order item
            $stmt = $connect->prepare("UPDATE order_items SET quantity = ?, price = ? WHERE order_id = ? AND menu_id = ?");
            $stmt->execute([$quantity, $price, $order_id, $menu_id]);

            $order_item_id = $existingItem['order_item_id'];

            // Update ingredients
            $stmt = $connect->prepare("SELECT ingredient_id, quantity_required FROM menu_ingredients WHERE menu_id = ?");
            $stmt->execute([$menu_id]);
            $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($ingredients as $ingredient) {
                $ingredient_id = $ingredient['ingredient_id'];
                $required_qty = $ingredient['quantity_required'] * $quantity;

                // Check if ingredient record exists
                $stmt = $connect->prepare("SELECT id FROM order_ingredients WHERE order_id = ? AND menu_id = ? AND ingredient_id = ?");
                $stmt->execute([$order_id, $menu_id, $ingredient_id]);
                $existingIngredient = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existingIngredient) {
                    // Update existing ingredient record
                    $stmt = $connect->prepare("UPDATE order_ingredients SET consumed_quantity = ?, quantity_required = ? WHERE order_id = ? AND menu_id = ? AND ingredient_id = ?");
                    $stmt->execute([$required_qty, $required_qty, $order_id, $menu_id, $ingredient_id]);
                } else {
                    // Insert new ingredient tracking record
                    $stmt = $connect->prepare("INSERT INTO order_ingredients (order_id, menu_id, ingredient_id, quantity_required, consumed_quantity) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$order_id, $menu_id, $ingredient_id, $required_qty, $required_qty]);
                }
            }
        } else {
            // Insert new order item
            $stmt = $connect->prepare("INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $menu_id, $quantity, $price]);
            $order_item_id = $connect->lastInsertId();

            // Insert new ingredients for the new item
            $stmt = $connect->prepare("SELECT ingredient_id, quantity_required FROM menu_ingredients WHERE menu_id = ?");
            $stmt->execute([$menu_id]);
            $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($ingredients as $ingredient) {
                $ingredient_id = $ingredient['ingredient_id'];
                $required_qty = $ingredient['quantity_required'] * $quantity;

                $stmt = $connect->prepare("INSERT INTO order_ingredients (order_id, menu_id, ingredient_id, quantity_required, consumed_quantity) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$order_id, $menu_id, $ingredient_id, $required_qty, $required_qty]);
            }
        }
    }

    // 2. Delete removed items and their ingredients
    foreach ($removedItems as $menu_id) {
        // Delete ingredients linked to the removed order items
        $stmt = $connect->prepare("DELETE FROM order_ingredients WHERE order_id = ? AND menu_id = ?");
        $stmt->execute([$order_id, $menu_id]);

        // Delete order items
        $stmt = $connect->prepare("DELETE FROM order_items WHERE order_id = ? AND menu_id = ?");
        $stmt->execute([$order_id, $menu_id]);
    }

    // 3. Update total price in orders
    $stmt = $connect->prepare("UPDATE orders SET total_price = ? WHERE order_id = ?");
    $stmt->execute([$total_price, $order_id]);

    $connect->commit();

    echo json_encode(["status" => "success", "message" => "Order updated successfully!", "order_id" => $order_id, "total_price" => $total_price]);
} catch (PDOException $e) {
    $connect->rollBack();
    die(json_encode(["status" => "error", "message" => $e->getMessage()]));
}
