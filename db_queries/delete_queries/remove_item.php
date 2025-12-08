<?php
include_once __DIR__ . '/../../connection.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $ingredient_id = isset($data['ingredient_id']) ? $data['ingredient_id'] : null;

    if ($ingredient_id) {
        try {
            $connect->beginTransaction();

            // 1. Get the supplier IDs related to the ingredient's batches (before deletion)
            $getSupplierIdsQuery = "SELECT DISTINCT supplier_id FROM stock_batches WHERE ingredient_id = :ingredient_id";
            $stmtGetSupplierIds = $connect->prepare($getSupplierIdsQuery);
            $stmtGetSupplierIds->execute([':ingredient_id' => $ingredient_id]);
            $supplierIds = $stmtGetSupplierIds->fetchAll(PDO::FETCH_COLUMN);

            // 2. Remove related records from stock_batches
            $deleteStockBatchQuery = "DELETE FROM stock_batches WHERE ingredient_id = :ingredient_id";
            $stmtDeleteStockBatch = $connect->prepare($deleteStockBatchQuery);
            $stmtDeleteStockBatch->execute([':ingredient_id' => $ingredient_id]);

            // 3. Remove the ingredient from inventory
            $deleteInventoryQuery = "DELETE FROM inventory WHERE ingredient_id = :ingredient_id";
            $stmtDeleteInventory = $connect->prepare($deleteInventoryQuery);
            $stmtDeleteInventory->execute([':ingredient_id' => $ingredient_id]);

            // 4. Check and delete unused suppliers
            foreach ($supplierIds as $supplier_id) {
                $checkSupplierUsageQuery = "SELECT COUNT(*) FROM stock_batches WHERE supplier_id = :supplier_id";
                $stmtCheckSupplier = $connect->prepare($checkSupplierUsageQuery);
                $stmtCheckSupplier->execute([':supplier_id' => $supplier_id]);
                $usageCount = $stmtCheckSupplier->fetchColumn();

                if ($usageCount == 0) {
                    $deleteSupplierQuery = "DELETE FROM suppliers WHERE supplier_id = :supplier_id";
                    $stmtDeleteSupplier = $connect->prepare($deleteSupplierQuery);
                    $stmtDeleteSupplier->execute([':supplier_id' => $supplier_id]);
                }
            }

            $connect->commit();
            echo json_encode(['success' => true, 'message' => 'Item and related data removed successfully.']);
        } catch (PDOException $e) {
            $connect->rollBack();
            echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
            logError("Database error: " . $e->getMessage(), "ERROR");
            http_response_code(500);  // Internal Server Error
        } catch (Exception $e) {
            $connect->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            logError("Remove item error: " . $e->getMessage(), "ERROR");
            http_response_code(500);  // Internal Server Error
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Ingredient ID not provided.']);
        http_response_code(400);  // Bad Request
        logError("Remove item error: Ingredient ID not provided.", "ERROR");
    }
}
