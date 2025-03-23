<?php
require_once '../../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ingredient_id = $_POST['ingredient_id'];
    $supplier_name = $_POST['supplier_name'];
    $stock_quantity = $_POST['stock_quantity'];
    $item_cost = $_POST['item_cost'];
    $expiration_date = $_POST['expiration_date'];

    try {
        // Start transaction
        $connect->beginTransaction();

        // ✅ Get the unit for the ingredient
        $unitQuery = "SELECT unit FROM ingredients WHERE ingredient_id = :ingredient_id";
        $stmtUnit = $connect->prepare($unitQuery);
        $stmtUnit->execute([':ingredient_id' => $ingredient_id]);
        $unit = $stmtUnit->fetchColumn();

        if (!$unit) {
            throw new Exception("Invalid ingredient ID. Unit not found.");
        }

        // Insert ingredient into inventory if it doesn't exist
        $inventoryQuery = "INSERT INTO inventory (ingredient_id, current_stock) 
                            VALUES (:ingredient_id, :stock_quantity) 
                            ON DUPLICATE KEY UPDATE current_stock = current_stock + :stock_quantity";
        $stmtInventory = $connect->prepare($inventoryQuery);
        $stmtInventory->execute([
            ':ingredient_id' => $ingredient_id,
            ':stock_quantity' => $stock_quantity
        ]);

        // Insert supplier (if not existing)
        $supplierQuery = "INSERT INTO suppliers (supplier_name) 
                            VALUES (:supplier_name) 
                            ON DUPLICATE KEY UPDATE supplier_name = VALUES(supplier_name)";
        $stmtSupplier = $connect->prepare($supplierQuery);
        $stmtSupplier->execute([':supplier_name' => $supplier_name]);

        // Get supplier ID
        $supplier_id = $connect->lastInsertId();
        if ($supplier_id == 0) {
            // If supplier already exists, fetch the ID
            $stmtSupplier = $connect->prepare("SELECT supplier_id FROM suppliers WHERE supplier_name = :supplier_name");
            $stmtSupplier->execute([':supplier_name' => $supplier_name]);
            $supplier_id = $stmtSupplier->fetchColumn();
        }

        // Insert stock batch (tracks expiration dates)
        $batchQuery = "INSERT INTO stock_batches (ingredient_id, supplier_id, quantity, cost, expiration_date) 
                        VALUES (:ingredient_id, :supplier_id, :stock_quantity, :item_cost, :expiration_date)";
        $stmtBatch = $connect->prepare($batchQuery);
        $stmtBatch->execute([
            ':ingredient_id' => $ingredient_id,
            ':supplier_id' => $supplier_id,
            ':stock_quantity' => $stock_quantity,
            ':item_cost' => $item_cost,
            ':expiration_date' => $expiration_date
        ]);

        // ✅ Insert into inventory_transactions (tracks restocking)
        $transactionQuery = "INSERT INTO inventory_transactions (ingredient_id, transaction_type, quantity, unit) 
                                VALUES (:ingredient_id, 'restock', :stock_quantity, :unit)";
        $stmtTransaction = $connect->prepare($transactionQuery);
        $stmtTransaction->execute([
            ':ingredient_id' => $ingredient_id,
            ':stock_quantity' => $stock_quantity,
            ':unit' => $unit
        ]);

        // Commit transaction
        $connect->commit();
        echo json_encode(['success' => true, 'message' => 'Stock added successfully and transaction recorded!']);

    } catch (PDOException $e) {
        $connect->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        $connect->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
