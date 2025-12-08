<?php
include_once __DIR__ . '/../../connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $menu_id = $_POST['menu_id'];
    $ingredient_id = $_POST['ingredient_id'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit'];

    try {
        $updateQuery = "UPDATE menu_ingredients
                SET ingredient_id = ?, quantity_required = ?, unit = ?
                WHERE menu_id = ? ";
        $stmt = $connect->prepare($updateQuery);
        $stmt->execute([$ingredient_id, $quantity, $unit, $menu_id]);


        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Ingredient updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes were made.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
