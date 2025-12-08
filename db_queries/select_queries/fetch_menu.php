<?php
include_once __DIR__ . '/../../connection.php';
require __DIR__ . '/../../components/logger.php';

$inputData = json_decode(file_get_contents("php://input"), true);

// Pagination parameters
$page  = isset($inputData['page']) ? (int)$inputData['page'] : 1;
$limit = isset($inputData['limit']) ? (int)$inputData['limit'] : 10;
$offset = ($page - 1) * $limit;

try {
    // 1️⃣ Get total menu count
    $countQuery = "SELECT COUNT(*) FROM menu";
    $stmt = $connect->prepare($countQuery);
    $stmt->execute();
    $totalRows = (int)$stmt->fetchColumn();

    // 2️⃣ Fetch paginated menu items
    $sql = "SELECT menu_id, name, category, menu_type, price, availability
            FROM menu
            ORDER BY date_added DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $connect->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($menus) {
        foreach ($menus as $menu) {
            $menuJson = htmlspecialchars(json_encode($menu), ENT_QUOTES, 'UTF-8');

            echo "<tr>
                    <td>{$menu['menu_id']}</td>
                    <td>{$menu['name']}</td>
                    <td>{$menu['category']}</td>
                    <td>{$menu['menu_type']}</td>
                    <td>₱ " . number_format($menu['price'], 2) . "</td>
                    <td>" . ($menu['availability'] ? 'Available' : 'Not Available') . "</td>
                    <td>
                        <button class='edit-btn edit-menu' data-id='{$menuJson}'>Edit</button>
                        <button class='delete-btn edit-btn delete-menu' data-id='{$menu['menu_id']}'>Delete</button>
                    </td>
                </tr>";
        }
    } else {
        echo "<tr><td colspan='7' style='text-align:center;'>No menu items found.</td></tr>";
    }

    // Send total rows as header (so your JS can read it)
    header('X-Total-Rows: ' . $totalRows);
} catch (Exception $e) {
    echo "<tr><td colspan='7' style='text-align:center; color: red;'>Error fetching menu: " . $e->getMessage() . "</td></tr>";
    logError("Error fetching menu: " . $e->getMessage(), "ERROR");
    http_response_code(500);
}
