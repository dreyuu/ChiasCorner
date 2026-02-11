<?php
include_once __DIR__ . '/../../connection.php';

include_once __DIR__ . '/../../components/system_log.php';
include_once __DIR__ . '/../../components/pusher_helper.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get and sanitize input
$ownerID = trim($_POST['owner_id'] ?? '');
$listIngredient = trim($_POST['list-ingredient'] ?? '');
$categoryIngredient = trim($_POST['category-ingredient'] ?? '');
$unitIngredient = trim($_POST['unit-ingredient'] ?? '');

// Validate required fields
if (empty($listIngredient) || empty($categoryIngredient) || empty($unitIngredient)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

try {
    // Begin transaction
    $connect->beginTransaction();

    // Check if ingredient already exists
    $checkQuery = "SELECT ingredient_id FROM ingredients WHERE ingredient_name = :ingredient_name LIMIT 1";
    $checkStmt = $connect->prepare($checkQuery);
    $checkStmt->bindParam(':ingredient_name', $listIngredient, PDO::PARAM_STR);
    $checkStmt->execute();

    if ($checkStmt->rowCount() > 0) {
        // Ingredient already exists
        $connect->rollBack();
        echo json_encode(['success' => false, 'message' => 'Ingredient already exists']);
        exit();
    }

    // Insert new ingredient
    $ingredientQuery = "INSERT INTO ingredients (ingredient_name, category, unit, date_added)
                        VALUES (:ingredient_name, :category, :unit, :date_added)";
    $stmtIngredient = $connect->prepare($ingredientQuery);
    $stmtIngredient->bindParam(':ingredient_name', $listIngredient, PDO::PARAM_STR);
    $stmtIngredient->bindParam(':category', $categoryIngredient, PDO::PARAM_STR);
    $stmtIngredient->bindParam(':unit', $unitIngredient, PDO::PARAM_STR);
    $stmtIngredient->bindParam(':date_added', localNow(), PDO::PARAM_STR);
    $stmtIngredient->execute();

    // Commit transaction
    $connect->commit();

    echo json_encode(['success' => true, 'message' => 'Ingredient added successfully!']);
    PusherHelper::send("ingredients-channel", "modify-ingredients", ["msg" => "Ingredients added successfully"]);
    logAction(
        $connect,
        $ownerID,        // admin who created the user
        'INGREDIENTS',          // NOT AUTH
        'ADD INGREDIENTS',   // specific action type
        "Ingredient Added: $listIngredient"
    );
} catch (PDOException $e) {
    $connect->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    logError("Database error inserting ingredient: " . $e->getMessage(), "ERROR");
} catch (Exception $e) {
    $connect->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    logError("Error inserting ingredient: " . $e->getMessage(), "ERROR");
}
