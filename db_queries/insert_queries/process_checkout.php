<?php
require '../../connection.php'; // Your database connection file

// Get JSON data from frontend
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["error" => "Invalid request"]);
    exit();
}

$order_id = $data['order_id'];
$amount_paid = $data['amount_paid'];
$payment_method = $data['payment_method'];
$consumed_ingredients = $data['consumed_ingredients'];

try {
    $connect->beginTransaction();

    // Step 1: Update consumed quantity in order_ingredients
    foreach ($consumed_ingredients as $ingredient) {
        $stmt = $connect->prepare("UPDATE order_ingredients 
                                SET quantity_required = :quantity 
                                WHERE order_id = :order_id AND ingredient_id = :ingredient_id");
        $stmt->execute([
            ':quantity' => $ingredient['quantity'],
            ':order_id' => $order_id,
            ':ingredient_id' => $ingredient['ingredient_id']
        ]);
    }

    // Step 2: Check Stock Availability
    foreach ($consumed_ingredients as $ingredient) {
        $stmt = $connect->prepare("SELECT current_stock FROM inventory WHERE ingredient_id = :ingredient_id");
        $stmt->execute([':ingredient_id' => $ingredient['ingredient_id']]);
        $stock = $stmt->fetchColumn();

        if ($stock === false || $stock < $ingredient['quantity']) {
            throw new Exception("Insufficient stock for ingredient ID: " . $ingredient['ingredient_id']);
        }
    }

    // Step 3: Deduct Inventory Stock
    foreach ($consumed_ingredients as $ingredient) {
        $stmt = $connect->prepare("UPDATE inventory SET current_stock = current_stock - :quantity WHERE ingredient_id = :ingredient_id");
        $stmt->execute([
            ':quantity' => $ingredient['quantity'],
            ':ingredient_id' => $ingredient['ingredient_id']
        ]);

        // Step 4: Insert Inventory Transaction
        $stmt = $connect->prepare("INSERT INTO inventory_transactions (ingredient_id, transaction_type, quantity, unit) 
                                VALUES (:ingredient_id, 'usage', :quantity, (SELECT unit FROM ingredients WHERE ingredient_id = :ingredient_id))");
        $stmt->execute([
            ':ingredient_id' => $ingredient['ingredient_id'],
            ':quantity' => $ingredient['quantity']
        ]);
    }

    // Step 5: Insert Payment Details
    $stmt = $connect->prepare("INSERT INTO payments (order_id, amount_paid, payment_method, payment_status) 
                            VALUES (:order_id, :amount_paid, :payment_method, 'paid')");
    $stmt->execute([
        ':order_id' => $order_id,
        ':amount_paid' => $amount_paid,
        ':payment_method' => $payment_method
    ]);

    // Step 6: Mark Order as Paid
    $stmt = $connect->prepare("UPDATE orders SET payment_status = 'paid', paid_amount = :amount_paid WHERE order_id = :order_id");
    $stmt->execute([
        ':amount_paid' => $amount_paid,
        ':order_id' => $order_id
    ]);

    // Step 7: Fetch ordered items as a comma-separated list
    $stmt = $connect->prepare("SELECT GROUP_CONCAT(m.name ORDER BY oi.order_item_id SEPARATOR ', ') AS items_ordered
        FROM order_items oi
        JOIN menu m ON oi.menu_id = m.menu_id
        WHERE oi.order_id = :order_id");
    $stmt->execute([':order_id' => $order_id]);
    $order_items = $stmt->fetch(PDO::FETCH_ASSOC);
    $items_ordered = $order_items['items_ordered'] ?? 'No items';

    // Step 7: Move Order to Order History with Ordered Items
    $stmt = $connect->prepare("INSERT INTO order_history (order_id, user_id, order_date, total_price, payment_status, paid_amount, discount_amount, items_ordered)
        SELECT o.order_id, o.user_id, o.order_date, o.total_price, o.payment_status, o.paid_amount, o.discount_amount, :items_ordered
        FROM orders o WHERE o.order_id = :order_id");
    $stmt->execute([
        ':order_id' => $order_id,
        ':items_ordered' => $items_ordered
    ]);

    // Step 8: Notify Admin if Stock is Low
    $lowStockMsg = "";
    foreach ($consumed_ingredients as $ingredient) {
        // Fetch ingredient name from ingredients table, not inventory
        $stmt = $connect->prepare("SELECT i.ingredient_name, inv.current_stock 
                                    FROM ingredients i 
                                    JOIN inventory inv ON i.ingredient_id = inv.ingredient_id
                                    WHERE inv.ingredient_id = :ingredient_id");
        $stmt->execute([':ingredient_id' => $ingredient['ingredient_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['current_stock'] <= 5) { // Adjust threshold as needed
            $lowStockMsg .= "Warning: Low stock for " . $row['ingredient_name'] . " (Remaining: " . $row['current_stock'] . ")\n";
        }
    }

    if (!empty($lowStockMsg)) {
        mail("admin@example.com", "Low Stock Alert", $lowStockMsg); // Replace with actual admin email
    }

    $connect->commit();

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    $connect->rollBack();
    echo json_encode(["error" => "Checkout failed: " . $e->getMessage()]);
}
?>
