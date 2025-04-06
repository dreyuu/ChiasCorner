<?php
include_once '../../connection.php';

try {
    // Get menu type from query parameters
    $menuType = isset($_GET['menu_type']) ? trim($_GET['menu_type']) : '';

    // Base query
    $query = "SELECT menu_id, name, category, price, availability, menu_image FROM menu";

    // Add condition if menuType is provided
    if (!empty($menuType)) {
        $query .= " WHERE category = :menuType";
    }

    $stmt = $connect->prepare($query);

    // Bind parameter if filtering by menu type
    if (!empty($menuType)) {
        $stmt->bindParam(':menuType', $menuType, PDO::PARAM_STR);
    }

    $stmt->execute();
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($menuItems) {
        foreach ($menuItems as $menu) {
            $menu_id = htmlspecialchars($menu['menu_id'], ENT_QUOTES);
            $name = htmlspecialchars($menu['name'], ENT_QUOTES);
            $category = htmlspecialchars($menu['category'], ENT_QUOTES);
            $price = number_format($menu['price'], 2);
            $availability = $menu['availability'] ? 'Available' : 'Not Available';
            
            // Image Path - Assuming images are stored in 'uploads/' with menu_id as filename
            $imagePath = "uploads/" . basename($menu['menu_image']); // Modify based on your actual file format
            if ($imagePath === "uploads/") {
                $imagePath = "uploads/default.png"; // Default image
            }

            echo "
            <div class='menu-item'>
                <input type='hidden' name='menu_id' value='$menu_id' id='menu_id'>
                <img src='$imagePath' alt='$name' class='menu_image'>
                <p id='item_name'>$name<br><strong id='item_price'>₱$price</strong></p>
                <div class='item-controls'>
                    <button class='minus-item'>➖</button>
                    <span class='item-quantity'>0</span>
                    <button class='plus-item'>➕</button>
                </div>
            </div>";
        }
    } else {
        echo "<p>No menu items available for this category.</p>";
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
