<?php
include_once __DIR__ . '/../../connection.php';

try {
    if (isset($_GET['ingredient_id'])) {
        // Fetch a single item for editing
        $ingredient_id = $_GET['ingredient_id'];

        $query = "SELECT
                    i.ingredient_id,
                    ing.ingredient_name,
                    ing.category,
                    SUM(sb.quantity) AS total_stock,  -- Sum of stock quantities in stock_batches
                    -- Get the nearest expiration date
                    (SELECT expiration_date
                        FROM stock_batches
                        WHERE ingredient_id = i.ingredient_id
                        ORDER BY expiration_date ASC
                        LIMIT 1) AS nearest_expiration_date,
                    -- Get the supplier with the most stock
                    (SELECT s.supplier_name
                        FROM stock_batches sb
                        JOIN suppliers s ON sb.supplier_id = s.supplier_id
                        WHERE sb.ingredient_id = i.ingredient_id
                        GROUP BY sb.supplier_id
                        ORDER BY SUM(sb.quantity) DESC
                        LIMIT 1) AS top_supplier,
                    SUM(COALESCE(sb.cost, 0)) AS total_cost  -- Total cost for all stock batches
                FROM inventory i
                JOIN ingredients ing ON i.ingredient_id = ing.ingredient_id
                JOIN stock_batches sb ON i.ingredient_id = sb.ingredient_id
                JOIN suppliers s ON sb.supplier_id = s.supplier_id
                WHERE i.ingredient_id = ?
                GROUP BY i.ingredient_id, ing.ingredient_name, ing.category";

        $stmt = $connect->prepare($query);
        $stmt->execute([$ingredient_id]);
        $inventoryData = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $inventoryData]);
    } else {
        // Fetch all items
        $query = "SELECT
                    i.ingredient_id,
                    ing.ingredient_name,
                    ing.category,
                    SUM(sb.quantity) AS total_stock,  -- Sum of stock quantities in stock_batches
                    -- Get the nearest expiration date
                    (SELECT expiration_date
                        FROM stock_batches
                        WHERE ingredient_id = i.ingredient_id
                        ORDER BY expiration_date ASC
                        LIMIT 1) AS nearest_expiration_date,
                    -- Get the supplier with the most stock
                    (SELECT s.supplier_name
                        FROM stock_batches sb
                        JOIN suppliers s ON sb.supplier_id = s.supplier_id
                        WHERE sb.ingredient_id = i.ingredient_id
                        GROUP BY sb.supplier_id
                        ORDER BY SUM(sb.quantity) DESC
                        LIMIT 1) AS top_supplier,
                    SUM(COALESCE(sb.cost, 0)) AS total_cost  -- Total cost for all stock batches
                FROM inventory i
                JOIN ingredients ing ON i.ingredient_id = ing.ingredient_id
                JOIN stock_batches sb ON i.ingredient_id = sb.ingredient_id
                JOIN suppliers s ON sb.supplier_id = s.supplier_id
                GROUP BY i.ingredient_id, ing.ingredient_name, ing.category
                ORDER BY ing.ingredient_name ASC";

        $stmt = $connect->prepare($query);
        $stmt->execute();
        $inventoryData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $inventoryData]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching data: ' . $e->getMessage()]);
}
