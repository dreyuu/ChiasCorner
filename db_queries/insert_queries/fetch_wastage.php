<?php
include_once '../../connection.php';

try {
    $today = date('Y-m-d');
    $threeDaysFromNow = date('Y-m-d', strtotime('+3 days'));

    // Step 1: Handle expired batches
    $query = "SELECT sb.batch_id, sb.ingredient_id, sb.quantity, sb.expiration_date
                FROM stock_batches sb
                WHERE sb.expiration_date < ?";
    $stmt = $connect->prepare($query);
    $stmt->execute([$today]);
    $expiredBatches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($expiredBatches as $batch) {
        $ingredientId = $batch['ingredient_id'];
        $quantity = $batch['quantity'];

        // Insert into inventory_transactions as 'wastage'
        $insertQuery = "INSERT INTO inventory_transactions (ingredient_id, transaction_type, quantity, unit)
                        VALUES (?, 'wastage', ?, 'pcs')";
        $connect->prepare($insertQuery)->execute([$ingredientId, $quantity]);

        // Update inventory stock
        $updateInventory = "UPDATE inventory 
                            SET current_stock = GREATEST(current_stock - ?, 0)
                            WHERE ingredient_id = ?";
        $connect->prepare($updateInventory)->execute([$quantity, $ingredientId]);

        // Delete the expired batch
        $connect->prepare("DELETE FROM stock_batches WHERE batch_id = ?")->execute([$batch['batch_id']]);
    }

    // Step 2: Remove inventory records with zero stock
    $connect->exec("DELETE FROM inventory WHERE current_stock = 0");

    // Step 3: Check for ingredients expiring within 3 days
    $warningQuery = "SELECT sb.ingredient_id, sb.expiration_date, i.ingredient_name AS ingredient_name
                        FROM stock_batches sb
                        JOIN ingredients i ON sb.ingredient_id = i.ingredient_id
                        WHERE sb.expiration_date BETWEEN ? AND ?";
    $stmt = $connect->prepare($warningQuery);
    $stmt->execute([$today, $threeDaysFromNow]);
    $nearExpiry = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($nearExpiry as $item) {
        $ingredientId = $item['ingredient_id'];
        $ingredientName = $item['ingredient_name'];
        $expiration = date('F j, Y', strtotime($item['expiration_date']));

        $message = "Ingredient '{$ingredientName}' is about to expire on {$expiration}.";

        // Avoid inserting duplicates by checking if the message already exists
        $check = $connect->prepare("SELECT COUNT(*) FROM notifications WHERE message = ? AND ingredient_id = ?");
        $check->execute([$message, $ingredientId]);
        $exists = $check->fetchColumn();

        if (!$exists) {
            $insertNotif = "INSERT INTO notifications (ingredient_id, message, status) VALUES (?, ?, 'unread')";
            $connect->prepare($insertNotif)->execute([$ingredientId, $message]);
        }
    }

    echo json_encode(['success' => true, 'message' => 'Expired batches cleaned up and near-expiry notifications added.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
