<?php
include_once __DIR__ . '/../../connection.php';
include_once __DIR__ . '../../../components/pusher_helper.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader

$response = ["success" => false];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $menu_id = $_POST['menu_id'] ?? null;

        if (!$menu_id) {
            throw new Exception("Menu ID is required.");
        }

        // Fetch the menu image to delete it
        $stmt = $connect->prepare("SELECT menu_image FROM menu WHERE menu_id = :menu_id");
        $stmt->bindParam(":menu_id", $menu_id, PDO::PARAM_INT);
        $stmt->execute();
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$menu) {
            throw new Exception("Menu item not found.");
        }

        $uploadDir = "../../uploads/";
        $imagePath = $uploadDir . $menu["menu_image"];

        // Delete the menu from the database
        $stmt = $connect->prepare("DELETE FROM menu WHERE menu_id = :menu_id");
        $stmt->bindParam(":menu_id", $menu_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Delete the image file
            if ($menu["menu_image"] && file_exists($imagePath)) {
                unlink($imagePath);
            }

            $response["success"] = true;

            PusherHelper::send('menu-channel', 'modify-menu', ['msg' => 'Menu deleted successfully']);
        } else {
            throw new Exception("Database error: Unable to delete menu.");
        }
    } catch (Exception $e) {
        $response["error"] = $e->getMessage();
        logError("Delete menu error: " . $e->getMessage(), "ERROR");
        http_response_code(500);  // Internal Server Error
    }
} else {
    $response["error"] = "Invalid request method.";
    http_response_code(405);  // Method Not Allowed
    logError("Invalid request method for delete_menu.php", "ERROR");
}

echo json_encode($response);
