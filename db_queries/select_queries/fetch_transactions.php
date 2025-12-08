<?php
include_once __DIR__ . '/../../connection.php';

$type = $_GET['type'] ?? '';

try {
    $stmt = null;
    $data = [];

    switch ($type) {
        case 'transactions':
            $query = "SELECT it.*, ing.ingredient_name
                        FROM inventory_transactions it
                        JOIN ingredients ing ON it.ingredient_id = ing.ingredient_id
                        WHERE it.transaction_type IN ('restock', 'usage')
                        ORDER BY it.transaction_date DESC";
            break;

        case 'wastage':
            $query = "SELECT it.*, ing.ingredient_name
                        FROM inventory_transactions it
                        JOIN ingredients ing ON it.ingredient_id = ing.ingredient_id
                        WHERE it.transaction_type = 'wastage'
                        ORDER BY it.transaction_date DESC";
            break;

        case 'payments':
            $query = "SELECT * FROM payments ORDER BY payment_date DESC";
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid data type requested.']);
            exit;
    }

    $stmt = $connect->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching data: ' . $e->getMessage()]);
}
