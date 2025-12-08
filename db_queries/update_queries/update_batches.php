<?php
include_once __DIR__ . '/../../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve action and other fields from the POST request
    $action = isset($_POST['actions']) ? $_POST['actions'] : null;
    $batch_id = isset($_POST['batch_id']) ? $_POST['batch_id'] : null;
    $ingredient_id = isset($_POST['item_id']) ? $_POST['item_id'] : null;
    $supplier_name = isset($_POST['suppliers_name']) ? $_POST['suppliers_name'] : null;  // supplier_name provided
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
                $supplier_id = $supplier['supplier_id'];  // Get the supplier_id from the query result
            } else {
                echo json_encode(['success' => false, 'message' => 'Supplier not found.']);
                exit;  // Exit if supplier is not found
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Supplier name is required.']);
            exit;  // Exit if no supplier name is provided
        }

        // Step 2: Perform the update (add or subtract stock)
        if ($action == 'add') {
            // Add stock batch: Update the quantity, and other fields
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
                ':supplier_id' => $supplier_id,  // Use the supplier_id here
                ':cost' => $cost,
                ':expiration_date' => $expiration_date,
                ':batch_id' => $batch_id
            ]);
            echo json_encode(['success' => true, 'message' => 'Batch quantity updated (added).']);
        } elseif ($action == 'subtract') {
            // Subtract stock batch: Subtract quantity, and potentially update other fields
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
                ':supplier_id' => $supplier_id,  // Use supplier_id here
                ':cost' => $cost,
                ':expiration_date' => $expiration_date,
                ':batch_id' => $batch_id
            ]);

            // Check if any rows were affected
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Batch quantity updated (subtracted).']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Insufficient quantity to subtract.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating batch: ' . $e->getMessage()]);
    }
}
