<?php
require '../../connection.php'; // Adjust the path as needed

try {
    // Fetch menu items from the database
    $stmt = $connect->prepare("SELECT menu_id, name, category, menu_type, price, availability FROM menu ORDER BY date_added DESC");
    $stmt->execute();
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($menus) {
        foreach ($menus as $menu) {
            echo "<tr>
                    <td>{$menu['menu_id']}</td>
                    <td>{$menu['name']}</td>
                    <td>{$menu['category']}</td>
                    <td>{$menu['menu_type']}</td>
                    <td>₱ " . number_format($menu['price'], 2) . "</td>
                    <td>" . ($menu['availability'] ? 'Available' : 'Not Available') . "</td>
                    <td>
                        <button class='edit-btn' data-id='{$menu['menu_id']}'>Edit</button>
                        <button class='delete-btn edit-btn' data-id='{$menu['menu_id']}'>Delete</button>
                    </td>
                </tr>";
        }
        
    } else {
        echo "<tr><td colspan='7' style='text-align:center;'>No menu items found.</td></tr>";
    }
} catch (Exception $e) {
    echo "<tr><td colspan='7' style='text-align:center; color: red;'>Error fetching menu: " . $e->getMessage() . "</td></tr>";
}
