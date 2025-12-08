<?php
include_once __DIR__ . '/../../connection.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $menu_id = $_POST['menu_id'];
    $ingredient_id = $_POST['ingredient_id'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit'];
    $ingredient_type = $_POST['ingredient_type'];

    try {
        // Check if ingredient already exists in the menu
        $checkQuery = "SELECT * FROM menu_ingredients WHERE menu_id = ? AND ingredient_id = ?";
        $stmt = $connect->prepare($checkQuery);
        $stmt->execute([$menu_id, $ingredient_id]);

        if ($stmt->rowCount() > 0) {
            // Update existing ingredient
            $updateQuery = "UPDATE menu_ingredients
                SET ingredient_id = ?, quantity_required = ?, unit = ?, ingredient_type = ?
                WHERE menu_id = ?";
            $stmt = $connect->prepare($updateQuery);
            $stmt->execute([$ingredient_id, $quantity, $unit, $ingredient_type, $menu_id]);
            echo json_encode(['success' => true, 'message' => 'Ingredient updated successfully.']);
        } else {
            // Insert new ingredient
            $insertQuery = "INSERT INTO menu_ingredients (menu_id, ingredient_id, quantity_required, unit, ingredient_type)
            VALUES (?, ?, ?, ?, ?)";
            $stmt = $connect->prepare($insertQuery);
            $stmt->execute([$menu_id, $ingredient_id, $quantity, $unit, $ingredient_type]);
            echo json_encode(['success' => true, 'message' => 'Ingredient added successfully.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
