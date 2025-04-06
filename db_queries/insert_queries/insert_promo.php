<?php
require '../../connection.php'; // Adjust path as needed

$response = ["success" => false];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Retrieve form data
        $promo_name = $_POST['promoName'] ?? null;
        $discount_type = $_POST['discount_type'] ?? null;
        $discount_value = $_POST['discount_value'] ?? null;
        $start_date = $_POST['start_date'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        $applicable_menu_id = $_POST['applicable_menu'] ?? null;

        // Validate inputs
        if (!$promo_name || !$discount_type || !$discount_value || !$start_date || !$end_date) {
            throw new Exception("All fields are required.");
        }

        if (!is_numeric($discount_value) || $discount_value <= 0) {
            throw new Exception("Invalid discount value.");
        }

        if ($start_date > $end_date) {
            throw new Exception("Start date must be before end date.");
        }

        // If "All" is selected, set applicable_menu_id to NULL
        $applicable_menu_id = ($applicable_menu_id === "") ? null : $applicable_menu_id;

        // Insert into the database
        $stmt = $connect->prepare("INSERT INTO promotions (name, discount_type, discount_value, start_date, end_date, applicable_menu_id) 
                                    VALUES (:name, :discount_type, :discount_value, :start_date, :end_date, :applicable_menu_id)");

        $stmt->bindParam(":name", $promo_name);
        $stmt->bindParam(":discount_type", $discount_type);
        $stmt->bindParam(":discount_value", $discount_value);
        $stmt->bindParam(":start_date", $start_date);
        $stmt->bindParam(":end_date", $end_date);
        $stmt->bindParam(":applicable_menu_id", $applicable_menu_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $response["success"] = true;
        } else {
            throw new Exception("Failed to insert promo.");
        }
    } catch (Exception $e) {
        $response["error"] = $e->getMessage();
    }
} else {
    $response["error"] = "Invalid request method.";
}

echo json_encode($response);
?>
