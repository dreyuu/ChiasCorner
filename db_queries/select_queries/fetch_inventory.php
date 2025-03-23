<?php
include_once '../../connection.php';

try {
    $query = "SELECT 
            i.ingredient_id, 
            ing.ingredient_name, 
            ing.category, 
            SUM(i.current_stock) AS total_stock, 
            GROUP_CONCAT(DISTINCT sb.expiration_date ORDER BY sb.expiration_date ASC SEPARATOR ' | ') AS expiration_dates, 
            GROUP_CONCAT(DISTINCT s.supplier_name ORDER BY s.supplier_name ASC SEPARATOR ' | ') AS suppliers, 
            SUM(COALESCE(sb.cost, 0)) AS total_cost
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
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching data: ' . $e->getMessage()]);
}
