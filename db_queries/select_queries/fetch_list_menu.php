<?php
include_once '../../connection.php';

try {
    // Get menu type from query parameters
    $menuType = isset($_GET['menu_type']) ? trim($_GET['menu_type']) : '';

    // Step 1: Prepare ingredient stock data (sum across batches)
    $stockQuery = "SELECT ingredient_id, SUM(quantity) AS total_stock FROM stock_batches GROUP BY ingredient_id";
    $stockStmt = $connect->prepare($stockQuery);
    $stockStmt->execute();
    $stockMap = [];
    while ($row = $stockStmt->fetch(PDO::FETCH_ASSOC)) {
        $stockMap[$row['ingredient_id']] = $row['total_stock'];
    }

    // Step 2: Get menu items
    $query = "SELECT menu_id, name, category, price, availability, menu_image FROM menu";
    if (!empty($menuType)) {
        $query .= " WHERE category = :menuType";
    }

    $stmt = $connect->prepare($query);
    if (!empty($menuType)) {
        $stmt->bindParam(':menuType', $menuType, PDO::PARAM_STR);
    }

    $stmt->execute();
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($menuItems) {
        foreach ($menuItems as $menu) {
            $menu_id = $menu['menu_id'];
            $name = htmlspecialchars($menu['name'], ENT_QUOTES);
            $category = htmlspecialchars($menu['category'], ENT_QUOTES);
            $price = number_format($menu['price'], 2);
            $availability = $menu['availability'] ? 'Available' : 'Not Available';

            // Check if ingredients are sufficient or exist
            $ingredientQuery = "SELECT ingredient_id, quantity_required FROM menu_ingredients WHERE menu_id = ?";
            $ingredientStmt = $connect->prepare($ingredientQuery);
            $ingredientStmt->execute([$menu_id]);
            $ingredients = $ingredientStmt->fetchAll(PDO::FETCH_ASSOC);

            $hasIngredients = count($ingredients) > 0;
            $sufficient = $hasIngredients;

            if ($hasIngredients) {
                foreach ($ingredients as $ing) {
                    $ing_id = $ing['ingredient_id'];
                    $required = $ing['quantity_required'];
                    $available = isset($stockMap[$ing_id]) ? $stockMap[$ing_id] : 0;

                    if ($available < $required) {
                        $sufficient = false;
                        break;
                    }
                }
            }

            // Image Path
            $imagePath = "uploads/" . basename($menu['menu_image']);
            if ($imagePath === "uploads/") {
                $imagePath = "uploads/default.png";
            }

            // Output the item
            echo "
            <div class='menu-item'>
                <input type='hidden' name='menu_id' value='$menu_id' id='menu_id'>
                <img src='$imagePath' alt='$name' class='menu_image'>
                <div class='menu-content'>
                    <div class='item-name' id='item_name'>
                        <h3>$name</h3>
                    </div>
                ";

            // if ($sufficient) {
                echo "
                    <div class='item-bottom'>
                        <strong class='item-price' id='item_price'>$price</strong>
                        <div class='item-controls'>
                            <button class='minus-item'>➖</button>
                            <span class='item-quantity'>0</span>
                            <button class='plus-item'>➕</button>
                        </div>
                    </div>
                </div>";
            // } else {
            //     echo "<p class='out-of-stock' style='color: red;'>⚠️ Not enough ingredients</p>";
            // }

            echo "</div>";
        }
    } else {
        echo "<p>No menu items available for this category.</p>";
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
