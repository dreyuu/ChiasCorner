<?php
include_once __DIR__ . '/../../connection.php';
include_once __DIR__ . '../../../components/pusher_helper.php';
include_once __DIR__ . '../../../components/system_log.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader

$response = ["success" => false];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $ownerID = $_POST['owner_id'] ?? null;
        $menu_id = $_POST['menu_id'] ?? null;
        $name = $_POST['name'] ?? null;
        $category = $_POST['category'] ?? null;
        $menu_type = $_POST['menu_type'] ?? null;
        $price = $_POST['price'] ?? null;
        $availability = $_POST['availability'] ?? null;

        // Ensure required fields are filled
        if (!$menu_id || !$name || !$category || !$menu_type || !$price || !$availability) {
            throw new Exception("All fields are required.");
        }

        // Fetch the existing image name
        $stmt = $connect->prepare("SELECT menu_image FROM menu WHERE menu_id = :menu_id");
        $stmt->bindParam(":menu_id", $menu_id);
        $stmt->execute();
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$menu) {
            throw new Exception("Menu item not found.");
        }

        $currentImage = $menu['menu_image'];
        $newImage = $currentImage; // Default to the existing image

        // Handle new image upload
        if (isset($_FILES["menu_image"]) && $_FILES["menu_image"]["error"] == 0) {
            $uploadDir = "../../uploads/";

            // Ensure the upload directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $imageName = time() . "_" . basename($_FILES["menu_image"]["name"]);
            $imagePath = $uploadDir . $imageName;

            // Validate image type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($_FILES["menu_image"]["tmp_name"]);

            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception("Invalid file type. Only JPG, PNG, and GIF are allowed.");
            }

            // Move the new file
            if (!move_uploaded_file($_FILES["menu_image"]["tmp_name"], $imagePath)) {
                throw new Exception("Failed to upload image.");
            }

            // Delete the old image if a new one is uploaded
            if ($currentImage && file_exists($uploadDir . $currentImage)) {
                unlink($uploadDir . $currentImage);
            }

            $newImage = $imageName; // Set new image name
        }

        // Update the database
        $stmt = $connect->prepare("UPDATE menu SET name = :name, category = :category, menu_type = :menu_type, price = :price, availability = :availability, menu_image = :menu_image WHERE menu_id = :menu_id");

        $stmt->bindParam(":menu_id", $menu_id);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":category", $category);
        $stmt->bindParam(":menu_type", $menu_type);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":availability", $availability, PDO::PARAM_BOOL); // Store as boolean
        $stmt->bindParam(":menu_image", $newImage);

        if ($stmt->execute()) {
            $response["success"] = true;

            PusherHelper::send('menu-channel', 'modify-menu', ['msg' => 'Menu updated successfully']);
            logAction(
                $connect,
                $ownerID,
                'MENU',
                'MENU_UPDATE',
                "Menu #$menu_id updated",
                $menu_id
            );
        } else {
            throw new Exception("Database error: Unable to update menu.");
        }
    } catch (Exception $e) {
        $response["error"] = $e->getMessage();
        logError("Update menu error: " . $e->getMessage(), "ERROR");
        http_response_code(500);  // Internal Server Error
    }
} else {
    $response["error"] = "Invalid request method.";
    http_response_code(405);  // Method Not Allowed
    logError("Invalid request method for menu update", "ERROR");
}

// Return JSON response
echo json_encode($response);
