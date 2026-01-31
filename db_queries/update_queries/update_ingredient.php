<?php
include_once __DIR__ . '/../../connection.php';

include_once __DIR__ . '/../../components/system_log.php';
include_once __DIR__ . '/../../components/pusher_helper.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader

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
            PusherHelper::send("ingredients-channel", "modify-ingredients", ["msg" => "Item updated successfully"]);
            logAction(
                $connect,
                $ownerID,        // admin who created the user
                'INGREDIENTS',          // NOT AUTH
                'UPDATE INGREDIENTS',   // specific action type
                "Item updated: $ingredient_id, Quantity: $quantity $unit"
            );
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes were made.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
