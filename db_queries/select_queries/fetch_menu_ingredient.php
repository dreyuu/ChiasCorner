<?php
include_once __DIR__ . '/../../connection.php';

if (isset($_GET['menu_id'])) {
    $menu_id = $_GET['menu_id'];

    try {
        $query = "SELECT mi.menu_id, i.ingredient_id, i.ingredient_name, i.category,
                            mi.unit, mi.quantity_required, mi.ingredient_type, i.date_added
                    FROM menu_ingredients mi
                    INNER JOIN ingredients i ON mi.ingredient_id = i.ingredient_id
                    WHERE mi.menu_id = ?";



        $stmt = $connect->prepare($query);
        $stmt->execute([$menu_id]);
        $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'ingredients' => $ingredients]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No menu ID provided']);
}
