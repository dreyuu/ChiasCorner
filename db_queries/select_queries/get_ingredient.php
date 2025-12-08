<?php
include_once __DIR__ . '/../../connection.php';

if (isset($_GET['id'])) {
    $ingredient_id = $_GET['id'];

    try {
        $stmt = $connect->prepare("SELECT * FROM ingredients WHERE ingredient_id = ?");
        $stmt->execute([$ingredient_id]);
        $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ingredient) {
            echo json_encode(["success" => true, "ingredient" => $ingredient]);
        } else {
            echo json_encode(["success" => false, "message" => "Ingredient not found."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
}
