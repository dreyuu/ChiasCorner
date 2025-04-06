<?php
include_once '../../connection.php';

if (isset($_GET['item_id']) || isset($_GET['batch_id'])) {
    $item_id = $_GET['item_id'] ?? null; // Optional item_id for fetching batches
    $batch_id = $_GET['batch_id'] ?? null; // Optional batch_id for specific batch fetching

    try {

        if ($item_id) {
            // Query to fetch batches for a specific item_id
            $query = "SELECT 
                        sb.batch_id, 
                        sb.ingredient_id, 
                        i.ingredient_name,  -- Adding ingredient_name from the ingredients table
                        sb.supplier_id, 
                        sb.quantity, 
                        sb.cost, 
                        sb.expiration_date, 
                        s.supplier_name 
                    FROM stock_batches sb
                    JOIN suppliers s ON sb.supplier_id = s.supplier_id
                    JOIN ingredients i ON sb.ingredient_id = i.ingredient_id  -- Join with ingredients table
                    WHERE sb.ingredient_id = ? 
                    ORDER BY sb.expiration_date ASC";

            $stmt = $connect->prepare($query);
            $stmt->execute([$item_id]);
            $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'batches' => $batches]);
        } else if ($batch_id) {
            // Query to fetch a specific batch by batch_id
            $query = "SELECT 
                        sb.batch_id, 
                        sb.ingredient_id, 
                        i.ingredient_name,  -- Adding ingredient_name from the ingredients table
                        sb.supplier_id, 
                        sb.quantity, 
                        sb.cost, 
                        sb.expiration_date, 
                        s.supplier_name 
                    FROM stock_batches sb
                    JOIN suppliers s ON sb.supplier_id = s.supplier_id
                    JOIN ingredients i ON sb.ingredient_id = i.ingredient_id  -- Join with ingredients table
                    WHERE sb.batch_id = ?";

            $stmt = $connect->prepare($query);
            $stmt->execute([$batch_id]); // Fixed to use $batch_id instead of $item_id
            $batch = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($batch) {
                echo json_encode(['success' => true, 'batch' => $batch]); // Return the single batch
            } else {
                echo json_encode(['success' => false, 'message' => 'Batch not found']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No valid parameters provided']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching data: ' . $e->getMessage()]);
    }
}
else {
    echo json_encode(['success' => false, 'message' => 'No item_id or batch_id provided']);
}
