<?php
include_once __DIR__ . '/../../connection.php';
include_once __DIR__ . '../../../components/pusher_helper.php';
include_once __DIR__ . '../../../components/system_log.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader


try {
    $response = ["success" => false];

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        try {
            $ownerID = $_POST['owner_id'] ?? null;
            $name = $_POST['name'] ?? null;
            $category = $_POST['category'] ?? null;
            $menu_type = $_POST['menu_type'] ?? null;
            $price = $_POST['price'] ?? null;
            $availability = $_POST['availability'] ?? null;

            // Ensure required fields are filled
            if (!$name || !$category || !$menu_type || $price === null || $price === '' || !$availability) {
                throw new Exception("All fields are required.");
            }
            if ($price < 0) {
                throw new Exception("Price cannot be negative.");
            }
            // Check if the menu item already exists
            $stmt = $connect->prepare("SELECT COUNT(*) FROM menu WHERE name = :name");
            $stmt->bindParam(":name", $name);
            $stmt->execute();
            $exists = $stmt->fetchColumn();

            if ($exists > 0) {
                throw new Exception("Menu item '$name' already exists.");
            }

            // Handle image upload
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

                // Move uploaded file
                if (!move_uploaded_file($_FILES["menu_image"]["tmp_name"], $imagePath)) {
                    throw new Exception("Failed to upload image.");
                }

                // Insert menu data into the database
                $stmt = $connect->prepare("INSERT INTO menu (name, category, menu_type, price, availability, menu_image) VALUES (:name, :category, :menu_type, :price, :availability, :menu_image)");

                $stmt->bindParam(":name", $name);
                $stmt->bindParam(":category", $category);
                $stmt->bindParam(":menu_type", $menu_type);
                $stmt->bindParam(":price", $price);
                $stmt->bindParam(":availability", $availability, PDO::PARAM_BOOL); // Store as boolean
                $stmt->bindParam(":menu_image", $imageName);

                if ($stmt->execute()) {
                    $response["success"] = true;
                    PusherHelper::send('menu-channel', 'modify-menu', ['msg' => 'Menu added successfully']);
                    logAction(
                        $connect,
                        $ownerID,
                        'MENU',
                        'MENU_CREATE',
                        "Menu $name created",
                    );
                } else {
                    throw new Exception("Database error: Unable to insert menu.");
                }
            } else {
                throw new Exception("No valid image uploaded.");
            }
        } catch (Exception $e) {
            $response["error"] = $e->getMessage();
        }
    } else {
        $response["error"] = "Invalid request method.";
    }

    // Return JSON response
    echo json_encode($response);
} catch (\Throwable $th) {
    echo json_encode(["error" => "Error inserting menu: " . $th->getMessage()]);
    logError("Error inserting menu: " . $th->getMessage(), "ERROR");
    http_response_code(500);  // Internal Server Error
}
