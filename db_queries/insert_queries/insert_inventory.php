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

        // ✅ Insert supplier (if not existing)
        $supplierQuery = "INSERT INTO suppliers (supplier_name) 
                            VALUES (:supplier_name) 
                            ON DUPLICATE KEY UPDATE supplier_name = VALUES(supplier_name)";
        $stmtSupplier = $connect->prepare($supplierQuery);
        $stmtSupplier->execute([':supplier_name' => $supplier_name]);

        // ✅ Get supplier ID
        $supplier_id = $connect->lastInsertId();
        if ($supplier_id == 0) {
            // If supplier already exists, fetch the ID
            $stmtSupplier = $connect->prepare("SELECT supplier_id FROM suppliers WHERE supplier_name = :supplier_name");
            $stmtSupplier->execute([':supplier_name' => $supplier_name]);
            $supplier_id = $stmtSupplier->fetchColumn();
        }

        // ✅ Ensure ingredient exists in inventory (initialize to 0 if not)
        $insertInventoryQuery = "INSERT IGNORE INTO inventory (ingredient_id, current_stock) 
                                    VALUES (:ingredient_id, 0)";
        $stmtInsertInventory = $connect->prepare($insertInventoryQuery);
        $stmtInsertInventory->execute([':ingredient_id' => $ingredient_id]);

        // ✅ Insert stock batch (now safe because ingredient exists in inventory)
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

        // ✅ Recalculate total quantity from stock_batches
        $totalQuery = "SELECT COALESCE(SUM(quantity), 0) FROM stock_batches WHERE ingredient_id = :ingredient_id";
        $stmtTotal = $connect->prepare($totalQuery);
        $stmtTotal->execute([':ingredient_id' => $ingredient_id]);
        $total_quantity = $stmtTotal->fetchColumn();

        // ✅ Update inventory with recalculated quantity
        $inventoryQuery = "UPDATE inventory SET current_stock = :total_quantity WHERE ingredient_id = :ingredient_id";
        $stmtInventory = $connect->prepare($inventoryQuery);
        $stmtInventory->execute([ 
            ':ingredient_id' => $ingredient_id,
            ':total_quantity' => $total_quantity
        ]);

        // ✅ Log restock in inventory_transactions
        $transactionQuery = "INSERT INTO inventory_transactions (ingredient_id, transaction_type, quantity, unit) 
                                VALUES (:ingredient_id, 'restock', :stock_quantity, :unit)";
        $stmtTransaction = $connect->prepare($transactionQuery);
        $stmtTransaction->execute([
            ':ingredient_id' => $ingredient_id,
            ':stock_quantity' => $stock_quantity,
            ':unit' => $unit
        ]);

        // ✅ Commit transaction
        $connect->commit();
        echo json_encode(['success' => true, 'message' => 'Stock added successfully and inventory recalculated!']);

    } catch (PDOException $e) {
        $connect->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        $connect->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
