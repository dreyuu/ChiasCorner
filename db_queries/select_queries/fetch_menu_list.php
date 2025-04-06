<?php
include '../../connection.php';

try {
    // Fetch menu items
    $menuQuery = "SELECT menu_id, name FROM menu";
    $stmt = $connect->prepare($menuQuery);
    $stmt->execute(); // Execute the statement
    $menuLists = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch ingredient items
    $ingredientQuery = "SELECT ingredient_id, ingredient_name FROM ingredients";
    $stmt2 = $connect->prepare($ingredientQuery);
    $stmt2->execute(); // Execute the statement
    $ingredientList = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON
    echo json_encode([
        'success' => true,
        'menuList' => $menuLists,
        'menuIngredientList' => $ingredientList
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
