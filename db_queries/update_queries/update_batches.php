<?php
include_once __DIR__ . '/../../connection.php';
include_once __DIR__ . '/../../components/system_log.php';
include_once __DIR__ . '/../../components/pusher_helper.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve action and other fields from the POST request
    $ownerID = isset($_POST['owner_id']) ? $_POST['owner_id'] : null;
    $action = isset($_POST['actions']) ? $_POST['actions'] : null;
    $batch_id = isset($_POST['batch_id']) ? $_POST['batch_id'] : null;
    $ingredient_id = isset($_POST['item_id']) ? $_POST['item_id'] : null;
    $supplier_name = isset($_POST['suppliers_name']) ? $_POST['suppliers_name'] : null;
    $quantity = isset($_POST['stocks_quantity']) ? $_POST['stocks_quantity'] : 0;
    $cost = isset($_POST['items_cost']) ? $_POST['items_cost'] : 0;
    $expiration_date = isset($_POST['expiration_dates']) ? $_POST['expiration_dates'] : null;

    try {
        // Step 1: Look up the supplier_id based on the supplier_name
        if ($supplier_name) {
            $query = "SELECT supplier_id FROM suppliers WHERE supplier_name = :supplier_name LIMIT 1";
            $stmt = $connect->prepare($query);
            $stmt->execute([':supplier_name' => $supplier_name]);
            $supplier = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($supplier) {
                $supplier_id = $supplier['supplier_id'];
            } else {
                echo json_encode(['success' => false, 'message' => 'Supplier not found.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Supplier name is required.']);
            exit;
        }

        // Step 2: Perform the update (add or subtract stock)
        if ($action == 'add') {
            // Add stock batch
            $query = "UPDATE stock_batches
                        SET quantity = quantity + :quantity,
                            ingredient_id = :ingredient_id,
                            supplier_id = :supplier_id,
                            cost = :cost,
                            expiration_date = :expiration_date
                        WHERE batch_id = :batch_id";
            $stmt = $connect->prepare($query);
            $stmt->execute([
                ':quantity' => $quantity,
                ':ingredient_id' => $ingredient_id,
                ':supplier_id' => $supplier_id,
                ':cost' => $cost,
                ':expiration_date' => $expiration_date,
                ':batch_id' => $batch_id
            ]);
            echo json_encode(['success' => true, 'message' => 'Batch quantity updated (added).']);

            // Insert transaction into inventory_transactions
            $query = "INSERT INTO inventory_transactions (ingredient_id, transaction_type, quantity, unit)
                        VALUES (:ingredient_id, 'restock', :quantity, 'kg')";
            $stmt = $connect->prepare($query);
            $stmt->execute([
                ':ingredient_id' => $ingredient_id,
                ':quantity' => $quantity
            ]);

            // Send push notification
            PusherHelper::send("inventory-channel", "modify-inventory", ["msg" => "Item added successfully"]);

            // Log action
            logAction(
                $connect,
                $ownerID,
                'INVENTORY',
                'ADD INVENTORY',
                "Item Added: $ingredient_id, Quantity: $quantity"
            );
        } elseif ($action == 'subtract') {
            // Subtract stock batch
            $query = "UPDATE stock_batches
                        SET quantity = quantity - :quantity,
                            ingredient_id = :ingredient_id,
                            supplier_id = :supplier_id,
                            cost = :cost,
                            expiration_date = :expiration_date
                        WHERE batch_id = :batch_id AND quantity >= :quantity";
            $stmt = $connect->prepare($query);
            $stmt->execute([
                ':quantity' => $quantity,
                ':ingredient_id' => $ingredient_id,
                ':supplier_id' => $supplier_id,
                ':cost' => $cost,
                ':expiration_date' => $expiration_date,
                ':batch_id' => $batch_id
            ]);

            // Check if any rows were affected
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Batch quantity updated (subtracted).']);

                // Insert transaction into inventory_transactions
                $query = "INSERT INTO inventory_transactions (ingredient_id, transaction_type, quantity, unit)
                            VALUES (:ingredient_id, 'usage', :quantity, 'kg')";
                $stmt = $connect->prepare($query);
                $stmt->execute([
                    ':ingredient_id' => $ingredient_id,
                    ':quantity' => $quantity
                ]);

                // If quantity is now 0, delete the batch
                $query = "SELECT quantity FROM stock_batches WHERE batch_id = :batch_id";
                $stmt = $connect->prepare($query);
                $stmt->execute([':batch_id' => $batch_id]);
                $batch = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($batch && $batch['quantity'] <= 0) {
                    $deleteQuery = "DELETE FROM stock_batches WHERE batch_id = :batch_id";
                    $deleteStmt = $connect->prepare($deleteQuery);
                    $deleteStmt->execute([':batch_id' => $batch_id]);
                }

                // Send push notification
                PusherHelper::send("inventory-channel", "modify-inventory", ["msg" => "Item subtracted successfully"]);

                // Log action
                logAction(
                    $connect,
                    $ownerID,
                    'INVENTORY',
                    'SUBTRACT INVENTORY',
                    "Item Subtracted: $ingredient_id, Quantity: $quantity"
                );
            } else {
                echo json_encode(['success' => false, 'message' => 'Insufficient quantity to subtract.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
            logError("Invalid action provided for updating batch: " . $action, "ERROR");
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating batch: ' . $e->getMessage()]);
        logError("Database error updating batch: " . $e->getMessage(), "ERROR");
    }
}
