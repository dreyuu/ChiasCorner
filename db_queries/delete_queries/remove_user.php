<?php
// Database connection using PDO (XAMPP or any other method)
include '../../connection.php';

try {
    // Check if the request is a DELETE method
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Get the user ID from the URL parameter
        $userId = $_GET['id'];

        // Prepare the SQL DELETE query using PDO
        $stmt = $connect->prepare("DELETE FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT); // Bind the user ID parameter

        // Execute the query
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Account removed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove account']);
        }
    }
} catch (PDOException $e) {
    // Handle any errors that occur during the execution of the query
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Close the PDO connection
$connect = null;
?>
