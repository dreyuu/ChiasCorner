<?php
include_once __DIR__ . '/../../connection.php';
if (isset($_GET['id'])) {
    $menu_id = $_GET['id'];

    try {
        $stmt = $connect->prepare("SELECT menu_id, name, category, menu_type, price, availability FROM menu WHERE menu_id = ?");
        $stmt->execute([$menu_id]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode($menu);
    } catch (Exception $e) {
        echo json_encode(["error" => "Error fetching menu: " . $e->getMessage()]);
    }
}
